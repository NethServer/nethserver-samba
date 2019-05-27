<!--
#
# Copyright (C) 2019 Nethesis S.r.l.
# http://www.nethesis.it - nethserver@nethesis.it
#
# This script is part of NethServer.
#
# NethServer is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License,
# or any later version.
#
# NethServer is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with NethServer.  If not, see COPYING.
#
-->

<style scoped>
.pd-indent {
    margin-left: 8px;
}
textarea {
    width: 100%;
    min-height: 7em;
    font-family: monospace;
}
select {
    padding: 0;
}

.alert-title {
    display: inline-block;
    margin-right: 1ex;
}

.inline-select {
  display: inline-block !important;
  margin: 5px;
}

.label-select {
  padding: 8px;
}

.label-acl {
  color: #363636;
  background: #f5f5f5;
}

.remove-acl {
  color: #363636;
}

.builtin-acl {
  color: #909090;
  cursor: not-allowed;
}
</style>

<template>
    <div v-bind:id="id" data-backdrop="static" class="modal" tabindex="-1" role="dialog" v-bind:aria-labelledby="id + 'Label'" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" aria-label="Close">
                        <span class="pficon pficon-close"></span>
                    </button>
                    <h4 class="modal-title" v-bind:id="id + 'Label'">
                        <span v-if="action == 'delete'"      >{{ $t('sharedfolders.delete_title', this.item) }}</span>
                        <span v-else-if="action == 'reset-permissions'" >{{ $t('sharedfolders.reset_permissions_title', this.item) }}</span>
                        <span v-else-if="action == 'migrate'" >{{ $t('sharedfolders.migrate_title', this.item) }}</span>
                        <span v-else-if="action == 'create'" >{{ $t('sharedfolders.create_title', this.item) }}</span>
                        <span v-else                         >{{ $t('sharedfolders.edit_title',   this.item) }}</span>
                    </h4>
                </div>

                <div v-if="action == 'delete'" class="modal-body">
                    <div class="alert alert-warning">
                        <span class="pficon pficon-warning-triangle-o"></span>
                        <strong class="alert-title">{{$t('sharedfolders.delete_warning_title', this.item)}}</strong>&#x20;
                        <i18n path="sharedfolders.delete_warning_message" tag="span">
                            <b place="name">{{ this.item.name }}</b>
                        </i18n>
                    </div>
                    <div>{{ $t('sharedfolders.delete_confirm_message', this.item) }}</div>
                </div>

                <div v-else-if="action == 'reset-permissions'" class="modal-body">
                    <div class="alert alert-warning">
                        <span class="pficon pficon-warning-triangle-o"></span>
                        <strong class="alert-title">{{$t('sharedfolders.reset_permissions_warning_title', this.item)}}</strong>&#x20;
                        <span>{{ $t('sharedfolders.reset_permissions_warning_message')}}</span>
                    </div>
                    <div>
                        <i18n path="sharedfolders.reset_permissions_confirm_message" tag="span">
                            <b place="name">{{ this.item.name }}</b>
                        </i18n>
                    </div>
                </div>

                <div v-else-if="action == 'migrate'" class="modal-body">
                    <div class="alert alert-warning">
                        <span class="pficon pficon-warning-triangle-o"></span>
                        <strong class="alert-title">{{$t('sharedfolders.migrate_warning_title', this.item)}}</strong>&#x20;
                        <span>{{ $t('sharedfolders.migrate_warning_message') }}</span>
                    </div>
                    <div>
                        <i18n path="sharedfolders.migrate_confirm_message" tag="span">
                            <b place="vhost">{{
                                item.migrateVhost == '__ANY__' ? $t('sharedfolders.vhost_default') : item.migrateVhost
                            }}</b>
                        </i18n>
                    </div>
                </div>

                <div v-else class="modal-body">
                    <div v-if="!vIsAd" class="alert alert-info">
                        <span class="pficon pficon-info"></span>
                        <strong class="alert-title">{{$t('sharedfolders.no_acls_support_title', this.item)}}</strong>&#x20;
                        <span>{{ $t('sharedfolders.no_acls_support_message') }}</span>
                    </div>

                    <form class="form-horizontal">
                        <div v-bind:class="['form-group', vErrors.name ? 'has-error' : '']">
                            <label class="col-sm-3 control-label" v-bind:for="id + '-ni'">{{ $t('sharedfolders.name_label', this.item) }}</label>
                            <div class="col-sm-9">
                                <input v-bind:disabled="action != 'create'" type="text" v-model="item.name" v-bind:id="id + '-ni'" class="form-control">
                                <span v-if="vErrors.name" class="help-block">{{ vErrors.name }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" v-bind:for="id + '-di'">{{ $t('sharedfolders.description_label') }}</label>
                            <div class="col-sm-9">
                                <input type="text" v-model="item.Description" v-bind:id="id + '-di'" class="form-control">
                            </div>
                        </div>
                        <div v-if="vIsAd" v-bind:class="['form-group', vErrors.OwningGroup ? 'has-error' : '']">
                            <label class="col-sm-3 control-label" v-bind:for="id + '-og'">{{ $t('sharedfolders.OwningGroup_label') }}</label>
                            <div class="col-sm-9">
                                <select 
                                    class="form-control" 
                                    v-bind:id="id + '-og'"
                                    v-model="item.OwningGroup"
                                    v-on:change="updateGownerAcl()"
                                >
                                    <option disabled value="">{{$t('sharedfolders.owner_null_label')}}</option>
                                    <option
                                        v-for="owner in vOwners"
                                        v-bind:value="owner"
                                    >{{ owner | shorten }}</option>
                                </select>
                                <span v-if="vErrors.OwningGroup" class="help-block">{{ vErrors.OwningGroup }}</span>
                            </div>
                        </div>
                        <div v-bind:class="['form-group', vErrors.acls ? 'has-error' : '']">
                            <label
                                class="col-sm-3 control-label"
                                >
                                    <span v-if="vAclSpinner" class="spinner spinner-xs spinner-inline form-spinner-vSpinner"></span>&#x20;
                                    {{$t('sharedfolders.Acl_label')}}
                            </label>
                            <div class="col-sm-9">
                              <suggestions v-if="vIsAd"
                                v-model="vSearchText"
                                v-bind:options="{debounce: 400, inputClass: 'form-control', placeholder: $t('sharedfolders.Acl_placeholder')}"
                                v-bind:onInputChange="searchSubjects"
                                v-bind:onItemSelected="addSubject"
                              >
                                <div slot="item" slot-scope="props" class="single-item">
                                  <span>
                                    {{ props.item | shorten }}
                                  </span>
                                </div>
                              </suggestions>

                              <ul class="list-inline compact">
                                <li v-if="vIsAd && item.acls.GOWNER">
                                    <span class="label label-info label-select label-acl">
                                        {{ $t('sharedfolders.OwningGroup_label') }}
                                        <span class="inline-select">
                                            <select v-model="item.acls.GOWNER" class="form-control">
                                                <option disabled value="">{{$t('sharedfolders.acl_null_label')}}</option>
                                                <option value="r">{{$t('sharedfolders.acl_read_label')}}</option>
                                                <option value="rw">{{$t('sharedfolders.acl_readwrite_label')}}</option>
                                            </select>
                                        </span>
                                        <span class="remove-item-inline builtin-acl" href="#">
                                          <span v-bind:aria-label="$t('sharedfolders.acl_builtin_message')" class="fa fa-lock"></span>
                                        </span>
                                    </span>
                                </li>
                                <li v-for="(acl, subject) in item.acls" v-bind:key="subject" class="mg-bottom-5" v-if="subject != 'GOWNER' && subject != 'EVERYONE'">
                                  <span class="label label-info label-select label-acl">
                                    {{ subject | shorten }}
                                    <span class="inline-select">
                                      <select
                                        class="form-control"
                                        v-model="item.acls[subject]"
                                      >
                                        <option disabled value="">{{$t('sharedfolders.acl_null_label')}}</option>
                                        <option value="r">{{$t('sharedfolders.acl_read_label')}}</option>
                                        <option value="rw">{{$t('sharedfolders.acl_readwrite_label')}}</option>
                                      </select>
                                    </span>
                                    <a v-on:click="removeSubject(subject)" class="remove-item-inline remove-acl" href="#">
                                      <span v-bind:aria-label="$t('sharedfolders.acl_remove_message')" class="fa fa-times"></span>
                                    </a>
                                  </span>
                                </li>
                                <li>
                                    <span class="label label-info label-select label-acl">
                                        {{ $t('sharedfolders.everyone_label') }}
                                        <doc-info
                                          v-bind:placement="'top'"
                                          v-bind:title="$t('sharedfolders.everyone_label')"
                                          v-bind:chapter="'everyone_info'"
                                          v-bind:inline="true"
                                        ></doc-info>
                                        <span class="inline-select">
                                            <select v-model="item.acls['EVERYONE']" class="form-control">
                                                <option value="r">{{$t('sharedfolders.acl_read_label')}}</option>
                                                <option value="rw">{{$t('sharedfolders.acl_readwrite_label')}}</option>
                                                <option
                                                    v-bind:disabled="!vIsAd || item.guestAccess == 'enabled'"
                                                    value=""
                                                >{{$t('sharedfolders.acl_none_label')}}</option>
                                            </select>
                                        </span>
                                        <span class="remove-item-inline builtin-acl" href="#">
                                          <span v-bind:aria-label="$t('sharedfolders.acl_builtin_message')" class="fa fa-lock"></span>
                                        </span>
                                    </span>
                                </li>
                              </ul>
                              <span v-if="vErrors.acls" class="help-block">{{ vErrors.acls }}</span>
                            </div>
                        </div>

                        <div v-if="vIsAd" class="form-group">
                            <label
                                class="col-sm-3 control-label"
                                v-bind:for="id + '-ga'"
                                >{{$t('sharedfolders.guestAccess_label')}}
                                <doc-info
                                  v-bind:placement="'top'"
                                  v-bind:title="$t('sharedfolders.guestAccess_label')"
                                  v-bind:chapter="'guestAccess_info'"
                                  v-bind:inline="true"
                                ></doc-info>
                            </label>
                            <div class="col-sm-9">
                                <input
                                    type="checkbox"
                                    class="form-control"
                                    true-value="enabled"
                                    false-value="disabled"
                                    v-model="item.guestAccess"
                                    v-bind:id="id + '-ga'"
                                    v-on:change="updateEveryoneAcl()"
                                >
                            </div>
                        </div>

                      <legend class="fields-section-header-pf" v-bind:aria-expanded="vAdvanced ? 'true' : 'false'">
                        <span
                            v-bind:class="['fa fa-angle-right field-section-toggle-pf', vAdvanced ? 'fa-angle-down' : '']"
                        ></span>
                        <a
                            class="field-section-toggle-pf"
                            v-on:click="() => { vAdvanced = !vAdvanced }"
                        >{{$t('advanced_mode')}}</a>
                      </legend>
                        
                      <div v-if="vAdvanced">
                        <div class="form-group">
                            <label
                                class="col-sm-3 control-label"
                                v-bind:for="id + '-sa'"
                                >{{ $t('sharedfolders.SmbAuditStatus_label') }}
                            </label>
                            <div class="col-sm-9">
                                <input type="checkbox" v-model="item.SmbAuditStatus" v-bind:id="id + '-sa'" class="form-control" true-value="enabled" false-value="disabled">
                            </div>
                        </div>
                        <div class="form-group">
                            <label
                                class="col-sm-3 control-label"
                                v-bind:for="id + '-sb'"
                                >{{$t('sharedfolders.SmbShareBrowseable_label')}}
                            </label>
                            <div class="col-sm-9">
                                <input type="checkbox" v-model="item.SmbShareBrowseable" v-bind:id="id + '-sb'" class="form-control" true-value="enabled" false-value="disabled">
                            </div>
                        </div>
                        <div class="form-group">
                            <label
                                class="col-sm-3 control-label"
                                v-bind:for="id + '-rb'"
                                >{{$t('sharedfolders.SmbRecycleBinStatus_label')}}
                            </label>
                            <div class="col-sm-9">
                              <toggle-button
                                class="min-toggle"
                                v-bind:width="40"
                                v-bind:height="20"
                                v-bind:sync="true"
                                v-bind:id="id + '-rb'"
                                v-bind:color="{checked: '#0088ce', unchecked: '#bbbbbb'}"
                                v-model="vSmbRecycleBinStatus"
                              />
                            </div>
                        </div>
                        <div v-if="vSmbRecycleBinStatus" class="form-group">
                            <label
                                class="col-sm-3 control-label"
                                v-bind:for="id + '-rv'"
                                >{{$t('sharedfolders.SmbRecycleBinVersionsStatus_label')}}
                            </label>
                            <div class="col-sm-9">
                                <input type="checkbox" v-model="item.SmbRecycleBinVersionsStatus" v-bind:id="id + '-rv'" class="form-control" true-value="enabled" false-value="disabled">
                            </div>
                        </div>
                      </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <span v-if="vSpinner" class="spinner spinner-xs spinner-inline form-spinner-vSpinner"></span>&#x20;
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ $t('modal_cancel_button') }}</button>
                    <button v-if="action == 'delete'" v-on:click="$emit('modal-save')" type="button" class="btn btn-danger" >{{ $t('modal_delete_button') }}</button>
                    <button v-else-if="action == 'reset-permissions'" v-on:click="$emit('modal-save')" type="button" class="btn btn-danger" >{{ $t('modal_reset_permissions_button') }}</button>
                    <button v-else-if="action == 'migrate'" v-on:click="$emit('modal-save')" type="button" class="btn btn-danger" >{{ $t('modal_migrate_button') }}</button>
                    <button v-else-if="action == 'create'" v-on:click="$emit('modal-save')" type="button" class="btn btn-primary">{{ $t('modal_create_button') }}</button>
                    <button v-else v-on:click="$emit('modal-save')" type="button" class="btn btn-primary">{{ $t('modal_edit_button') }}</button>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
import execp from '@/execp'

const subjectsStore = {
    loaded: false,
    items: [],
}

export default {
    name: "SharedFolderEditModal",
    props: {
        'id': String,
        'action': String,
        'initialItem': Object,
        'groupsList': Array,
        'accountsProvider': String,
    },
    watch: {
        initialItem: function(newVal) {
            Object.assign(this.item, newVal);
            this.vErrors = {}
            this.vAdvanced = false
            this.vSearchText = ''
        },
    },
    computed: {
        vOwners: function() {
            return [].concat(this.$props.groupsList).sort()
        },
        vSmbRecycleBinStatus: {
            get: function() {
                return this.item.SmbRecycleBinStatus == 'enabled'
            },
            set: function(newValue) {
                this.item.SmbRecycleBinStatus = newValue ? 'enabled' : 'disabled'
            }
        },
        vIsAd: function() {
            return this.accountsProvider == 'ad'
        }
    },
    data() {
        return {
            vErrors: {},
            vSpinner: false,
            vAclSpinner: false,
            vAdvanced: false,
            vSearchText: '',

            item: {
                SmbRecycleBinStatus: "disabled",
                SmbRecycleBinVersionStatus: "disabled",
                SmbAuditStatus: "disabled",
                SmbShareBrowseable: "enabled",
                guestAccess: "disabled",
                name: "",
                OwningGroup: "",
                acls: {EVERYONE:""},
                Description: "",
            }
        }
    },
    mounted: function() {
        this.$on('modal-save', (eventData) => {
            this.vSpinner = true
            var inputData = {
                action: this.$props['action'],
                item: Object.assign({}, this.item),
            }

            this.vErrors = {}
            execp("nethserver-samba/sharedfolders/validate", inputData)
            .catch((validationError) => {
                this.vErrors = Object.fromEntries(validationError.attributes.map(err => [
                    err.parameter,
                    this.$t('validation.' + err.message.split("\n").pop(), {
                        value: err.value,
                        msg: err.message.split("\n").slice(0, -1)
                    })
                ]))
                this.vSpinner = false
                return Promise.reject(validationError) // still unresolved
            })
            .then((validationResult) => {
                this.vSpinner = false
                window.jQuery(this.$el).modal('hide') // on successful resolution close the dialog
                nethserver.notifications.success = this.$t("sharedfolders.item_" + this.action + "_ok");
                nethserver.notifications.error = this.$t("sharedfolders.item_" + this.action + "_error");
                return execp("nethserver-samba/sharedfolders/update", inputData, true) // start another async call
            })
            .finally(() => {
                this.vSpinner = false // always stop the spinner when async calls end
            })
            .then(() => {
                this.$emit('modal-close', eventData) // on save success, close the dialog
            })
        })
    },
    methods: {
        searchSubjects(query) {
            query = query.toLowerCase();
            if(subjectsStore.loaded) {
                return Promise.resolve(subjectsStore.items.filter(subj => subj.toLowerCase().includes(query)))
            } else {
                this.vAclSpinner = true
                return execp("nethserver-samba/sharedfolders/read", {"action":"list-users"})
                    .then(response => {
                        this.vAclSpinner = false
                        subjectsStore.loaded = true
                        subjectsStore.items = [].concat(response.users, this.$props.groupsList).sort()
                        return subjectsStore.items.filter(subj => subj.toLowerCase().includes(query))
                    })
            }
        },
        addSubject(subject) {
            if( ! this.item.acls[subject] && this.item.OwningGroup != subject) {
                this.$set(this.item.acls, subject, 'r') // see Vuejs "reactivity in depth"
            }
        },
        removeSubject(subject) {
            if(this.item.acls[subject]) {
                this.$delete(this.item.acls, subject)
            }
        },
        updateGownerAcl() {
            if( ! this.item.acls['GOWNER']) {
                this.$set(this.item.acls, 'GOWNER', 'rw')
            }
        },
        updateEveryoneAcl() {
            if( ! this.item.acls['EVERYONE']) {
                this.$set(this.item.acls, 'EVERYONE', 'r')
            }
        },
    },
    filters: {
        shorten(subject) {
            return subject.replace(/@.*$/, '');
        },
    },
}
</script>
