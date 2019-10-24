<!--
/*
 * Copyright (C) 2019 Nethesis S.r.l.
 * http://www.nethesis.it - nethserver@nethesis.it
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License,
 * or any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see COPYING.
 */
-->

<template>
  <div>
    <h1>{{ $t('audit.title') }}</h1>
    <doc-info
      :placement="'top'"
      :title="$t('docs.audit')"
      :chapter="'shared_folder'"
      :section="''"
      :inline="false"
    ></doc-info>

    <div v-if="!view.isLoaded" class="spinner spinner-lg view-spinner"></div>

    <div v-show="!view.menu.installed && view.isLoaded">
      <div class="blank-slate-pf">
        <div class="blank-slate-pf-icon">
          <span class="pficon pficon pficon-add-circle-o"></span>
        </div>
        <h1>{{$t('package_required')}}</h1>
        <p>{{$t('package_required_desc')}}.</p>
        <pre>{{view.menu.packages.join(' ')}}</pre>
        <div class="blank-slate-pf-main-action">
          <button
            :disabled="view.isInstalling"
            @click="installPackages()"
            class="btn btn-primary btn-lg"
          >{{view.menu.packages.length == 1 ? $t('install_package') : $t('install_packages')}}</button>
          <div v-if="view.isInstalling" class="spinner spinner-sm"></div>
        </div>
      </div>
    </div>

    <h3 v-if="view.menu.installed &&  view.isLoaded">{{ $t('actions_title') }}</h3>
    <form
      v-if="view.menu.installed &&  view.isLoaded"
      role="form"
      class="search-pf has-button search"
    >
      <div class="form-group">
        <button
          class="btn btn-primary btn-lg margin-left-md"
          type="button"
          v-on:click="updateAudits()"
        >{{$t('audit.update')}}</button>
      </div>
    </form>

    <h3 v-if="view.menu.installed && view.isLoaded">{{ $t('audit.filter') }}</h3>
    <form
      v-show="view.menu.installed &&  view.isLoaded"
      role="form"
      class="form-horizontal"
      v-on:submit.prevent="getAudits(false)"
    >
      <div class="form-group">
        <label class="col-sm-2">{{$t('audit.message')}}</label>
        <div class="col-sm-6">
          <input type="text" v-model="filter.message" class="form-control" />
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2"></label>
        <div class="col-sm-6">
          <button class="btn btn-primary">{{$t('audit.filter')}}</button>
        </div>
      </div>
    </form>

    <h4 class="right gray">
      {{$t('audit.update_at')}}
      <b>{{view.updatedAt | dateFormat}}</b>
    </h4>
    <h3 v-if="view.menu.installed &&  view.isLoaded">{{ $t('list') }}</h3>
    <vue-good-table
      v-show="view.menu.installed && view.isLoaded"
      :customRowsPerPageDropdown="[25,50,100]"
      :perPage="25"
      :columns="auditColumns"
      :rows="auditRows"
      :lineNumbers="false"
      :defaultSortBy="{field: 'when', type: 'desc'}"
      :globalSearch="true"
      :paginate="true"
      styleClass="table"
      :nextText="tableLangsTexts.nextText"
      :prevText="tableLangsTexts.prevText"
      :rowsPerPageText="tableLangsTexts.rowsPerPageText"
      :globalSearchPlaceholder="tableLangsTexts.globalSearchPlaceholder"
      :ofText="tableLangsTexts.ofText"
    >
      <template slot="table-row" slot-scope="props">
        <td class="fancy">
          <span class="fa fa-clock-o span-right-icon"></span>
          {{props.row.when}}
        </td>
        <td class="fancy quota-min-width">
          <span class="fa fa-user span-right-icon"></span>
          {{props.row.user}}
        </td>
        <td class="fancy">
          <span class="fa fa-share span-right-icon"></span>
          {{props.row.share}}
        </td>
        <td>
          <span class="fa fa-cog span-right-icon"></span>
          <b>{{props.row.op | uppercase}}</b>
          :
          <code :title="props.row.arg">{{props.row.arg | ellipsis}}</code>
        </td>
        <td class="fancy">
          <a @click="openDetails(props.row)">{{$t('audit.details')}}</a>
        </td>
      </template>
    </vue-good-table>

    <div class="modal" id="auditDetailsModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{$t('audit.show_audit_details')}}</h4>
          </div>
          <div class="modal-body">
            <form class="form-horizontal" v-on:submit.prevent="getAudits(true, currentDetails)">
              <div class="form-group">
                <label class="col-sm-3">{{$t('audit.user')}}</label>
                <div class="col-sm-10">
                  <span>{{currentDetails.user}}</span>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3">{{$t('audit.share')}}</label>
                <div class="col-sm-10">
                  <code>{{currentDetails.share}}</code>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3">{{$t('audit.message')}}</label>
                <div class="col-sm-10">
                  <code>{{currentDetails.arg}}</code>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3">{{$t('audit.from')}}</label>
                <div class="col-sm-6">
                  <div id="date-picker" class="input-group date">
                    <input
                      v-model="currentDetails.from"
                      type="text"
                      placeholder="YYYY-MM-DD HH:mm:ss"
                      class="form-control bootstrap-datepicker"
                      :disabled="!view.isLoadedDetails"
                    />
                    <span class="input-group-addon">
                      <span class="fa fa-calendar"></span>
                    </span>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3">{{$t('audit.to')}}</label>
                <div class="col-sm-6">
                  <div id="date-picker" class="input-group date">
                    <input
                      v-model="currentDetails.to"
                      type="text"
                      placeholder="YYYY-MM-DD HH:mm:ss"
                      class="form-control bootstrap-datepicker"
                      :disabled="!view.isLoadedDetails"
                    />
                    <span class="input-group-addon">
                      <span class="fa fa-calendar"></span>
                    </span>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2"></label>
                <div class="col-sm-6">
                  <button class="btn btn-primary">{{$t('audit.filter')}}</button>
                </div>
              </div>
              <div v-if="!view.isLoadedDetails" class="spinner spinner-lg"></div>
              <vue-good-table
                v-show="view.isLoadedDetails"
                :customRowsPerPageDropdown="[5,10,25,50,100]"
                :perPage="5"
                :columns="auditColumnsDetails"
                :rows="auditRowsDetails"
                :lineNumbers="false"
                :defaultSortBy="{field: 'when', type: 'desc'}"
                :globalSearch="true"
                :paginate="true"
                styleClass="table"
                :nextText="tableLangsTexts.nextText"
                :prevText="tableLangsTexts.prevText"
                :rowsPerPageText="tableLangsTexts.rowsPerPageText"
                :globalSearchPlaceholder="tableLangsTexts.globalSearchPlaceholder"
                :ofText="tableLangsTexts.ofText"
              >
                <template slot="table-row" slot-scope="props">
                  <td class="fancy">
                    <span class="fa fa-clock-o span-right-icon"></span>
                    {{props.row.when}}
                  </td>
                  <td>
                    <span class="fa fa-cog span-right-icon"></span>
                    <b>{{currentDetails.op | uppercase}}</b>
                  </td>
                </template>
              </vue-good-table>
            </form>
          </div>
          <div class="modal-footer">
            <button class="btn btn-default" type="button" data-dismiss="modal">{{$t('cancel')}}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
var moment = require("moment");

export default {
  name: "Audit",
  beforeRouteEnter(to, from, next) {
    next(vm => {
      vm.view.isLoaded = false;
      nethserver.exec(
        ["nethserver-samba/feature/read"],
        {
          name: vm.$route.path.substr(1)
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }

          vm.view.menu = success;
        },
        function(error) {
          console.error(error);
        },
        false
      );
    });
  },
  mounted() {
    window.jQuery(".selectpicker").selectpicker();
    this.getAudits(false);
  },
  data() {
    return {
      view: {
        isLoaded: false,
        isLoadedDetails: false,
        isInstalling: false,
        menu: {
          installed: false,
          packages: []
        },
        updatedAt: ""
      },
      tableLangsTexts: this.tableLangs(),
      auditColumns: [
        {
          label: this.$i18n.t("audit.date"),
          field: "when",
          filterable: true
        },
        {
          label: this.$i18n.t("audit.user"),
          field: "user",
          filterable: true
        },
        {
          label: this.$i18n.t("audit.share"),
          field: "share",
          filterable: true
        },
        {
          label: this.$i18n.t("action"),
          field: "arg",
          filterable: true
        },
        {
          label: this.$i18n.t("details"),
          field: "",
          filterable: false
        }
      ],
      auditRows: [],
      auditColumnsDetails: [
        {
          label: this.$i18n.t("audit.date"),
          field: "when",
          filterable: true
        },
        {
          label: this.$i18n.t("action"),
          field: "arg",
          filterable: true
        }
      ],
      auditRowsDetails: [],
      filter: {
        user: "",
        address: "",
        share: "",
        operation: "",
        message: "",
        from: moment()
          .startOf("day")
          .format("YYYY-MM-DD HH:mm"),
        to: moment()
          .endOf("day")
          .format("YYYY-MM-DD HH:mm")
      },
      currentDetails: {}
    };
  },
  methods: {
    installPackages() {
      this.view.isInstalling = true;
      // notification
      nethserver.notifications.success = this.$i18n.t("packages_installed_ok");
      nethserver.notifications.error = this.$i18n.t("packages_installed_error");

      nethserver.exec(
        ["nethserver-samba/feature/update"],
        {
          name: this.$route.path.substr(1)
        },
        function(stream) {
          console.info("install-package", stream);
        },
        function(success) {
          // reload page
          window.parent.location.reload();
        },
        function(error) {
          console.error(error);
        }
      );
    },
    getAudits(details, obj) {
      var context = this;

      if (!details) {
        context.view.isLoaded = false;
      } else {
        context.view.isLoadedDetails = false;
      }
      nethserver.exec(
        ["nethserver-samba/audit/read"],
        {
          action: details ? "file-access-details" : "file-access",
          username: details ? obj.user : "",
          share: details ? obj.share : "",
          message: details ? obj.arg : context.filter.message,
          from: details ? moment(obj.from).unix() : "",
          to: details ? moment(obj.to).unix() : ""
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }
          if (!details) {
            context.auditRows = success.list;
            context.view.updatedAt = parseInt(success.updated);
            context.view.isLoaded = true;
          } else {
            context.auditRowsDetails = success;
            context.view.isLoadedDetails = true;
          }
        },
        function(error) {
          console.error(error);
          context.view.isLoaded = true;
        }
      );
    },
    updateAudits() {
      var context = this;
      // notification
      nethserver.notifications.success = this.$i18n.t("audit.updated_ok");
      nethserver.notifications.error = this.$i18n.t("audit.updated_error");

      nethserver.exec(
        ["nethserver-samba/audit/update"],
        null,
        function(stream) {
          console.info("updated", stream);
        },
        function(success) {
          context.getAudits(false);
        },
        function(error) {
          console.error(error);
        }
      );
    },
    openDetails(obj) {
      this.currentDetails = obj;
      this.currentDetails.from = moment(obj.when)
        .startOf("day")
        .format("YYYY-MM-DD HH:mm");
      this.currentDetails.to = moment(obj.when)
        .endOf("day")
        .format("YYYY-MM-DD HH:mm");
      this.auditRowsDetails = [];
      this.getAudits(true, obj);

      $("#auditDetailsModal").modal("show");
    }
  }
};
</script>

<style>
.span-right-icon {
  font-size: 15px;
  margin-right: 8px;
}

.margin-left-md {
  margin-left: 10px !important;
}
</style>