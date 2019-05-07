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
input[type=radio].form-control, input[type=checkbox].form-control {
    width: 12px !important;
    height: 12px !important;
    display: inline-block;
    vertical-align: -25%;
    margin-right: 1em;
}
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

                <div v-else class="modal-body">
                    <form class="form-horizontal">
                        <div v-bind:class="['form-group', vErrors.name ? 'has-error' : '']">
                            <label class="col-sm-3 control-label" v-bind:for="id + '-ni'">{{ $t('sharedfolders.name_label', this.item) }}</label>
                            <div class="col-sm-9">
                                <input :disabled="action != 'create'" type="text" v-model="name" v-bind:id="id + '-ni'" class="form-control">
                                <span v-if="vErrors.name" class="help-block">{{ vErrors.name }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" v-bind:for="id + '-di'">{{ $t('sharedfolders.description_label', this.item) }}</label>
                            <div class="col-sm-9">
                                <input type="text" v-model="Description" v-bind:id="id + '-di'" class="form-control">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <span v-if="vSpinner" class="spinner spinner-xs spinner-inline form-spinner-vSpinner"></span>&#x20;
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ $t('modal_cancel_button') }}</button>
                    <button v-if="action == 'delete'" v-on:click="$emit('modal-save')" type="button" class="btn btn-danger" >{{ $t('modal_delete_button') }}</button>
                    <button v-else-if="action == 'create'" v-on:click="$emit('modal-save')" type="button" class="btn btn-primary">{{ $t('modal_create_button') }}</button>
                    <button v-else v-on:click="$emit('modal-save')" type="button" class="btn btn-primary">{{ $t('modal_edit_button') }}</button>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
import execp from '@/execp'

var attrs = [
  "Description",
  "GroupAccess",
  "OtherAccess",
  "OwningGroup",
  "SmbGuestAccessType",
  "SmbRecycleBinStatus",
  "SmbShareBrowseable",
  "acls",
  "name",
];

export default {
    name: "SharedFolderEditModal",
    props: {
        'id': String,
        'action': String,
        'item': Object,
    },
    watch: {
        item: function(newval) {
            this.vErrors = {}
            for(let i in attrs) {
                this[attrs[i]] = newval[attrs[i]] || "";
            }
        },
    },
    data() {
        var obj = {
            vErrors: {},
            vSpinner: false
        }
        for(let i in attrs) {
            obj[attrs[i]] = ""
        }
        return obj
    },
    mounted: function() {
        this.$on('modal-save', (eventData) => {
            this.vSpinner = true
            var inputData = {
                action: this.$props['action'],
                item: {},
            }
            for(let i in attrs) {
                inputData.item[attrs[i]] = this[attrs[i]]
            }
            this.vErrors = {}
            execp("nethserver-samba/sharedfolders/validate", inputData)
            .catch((validationError) => {
                let err = {}
                for(let i in validationError.attributes) {
                    let attr = validationError.attributes[i]
                    err[attr.parameter] = this.$t('validation.' + attr.error)
                }
                this.vErrors = err
                this.vSpinner = false
                return Promise.reject(validationError) // still unresolved
            })
            .then((validationResult) => {
                this.vSpinner = false
                window.jQuery(this.$el).modal('hide') // on successful resolution close the dialog

                nethserver.notifications.success = this.$t(
                    "sharedfolders.item_" +
                    (this.action == 'create' ? "created" : "updated") +
                    "_ok"
                );
                nethserver.notifications.error = this.$t(
                    "sharedfolders.item_" +
                    (this.action == 'create' ? "created" : "updated") +
                    "_error"
                );

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
    },
}
</script>
