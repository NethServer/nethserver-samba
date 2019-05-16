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

    <h3 v-if=" view.isLoaded">{{ $t('audit.filter') }}</h3>
    <form
      v-show=" view.isLoaded"
      role="form"
      class="form-horizontal"
      v-on:submit.prevent="getAudits()"
    >
      <div class="form-group">
        <label class="col-sm-2">{{$t('audit.user')}}</label>
        <div class="col-sm-6">
          <input type="text" v-model="filter.username" class="form-control">
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2">{{$t('audit.address')}}</label>
        <div class="col-sm-6">
          <input type="text" v-model="filter.address" class="form-control">
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2">{{$t('audit.share')}}</label>
        <div class="col-sm-6">
          <input type="text" v-model="filter.share" class="form-control">
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2">{{$t('audit.message')}}</label>
        <div class="col-sm-6">
          <input type="text" v-model="filter.message" class="form-control">
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2">{{$t('action')}}</label>
        <div class="col-sm-6">
          <select class="form-control selectpicker" v-model="filter.operation">
            <option value>{{$t('audit.all')}}</option>
            <option value="read-file">{{$t('audit.read_file')}}</option>
            <option value="delete-file">{{$t('audit.delete_file')}}</option>
            <option value="create-directory">{{$t('audit.create_directory')}}</option>
            <option value="delete-directory">{{$t('audit.delete_directory')}}</option>
            <option value="rename">{{$t('audit.rename')}}</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2">{{$t('audit.from')}}</label>
        <div class="col-sm-6">
          <div id="date-picker" class="input-group date">
            <input
              v-model="filter.from"
              type="text"
              placeholder="YYYY-MM-DD"
              class="form-control bootstrap-datepicker"
            >
            <span class="input-group-addon">
              <span class="fa fa-calendar"></span>
            </span>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2">{{$t('audit.to')}}</label>
        <div class="col-sm-6">
          <div id="date-picker" class="input-group date">
            <input
              v-model="filter.to"
              type="text"
              placeholder="YYYY-MM-DD"
              class="form-control bootstrap-datepicker"
            >
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
    </form>

    <h3 v-if=" view.isLoaded">{{ $t('actions_title') }}</h3>
    <form v-if=" view.isLoaded" role="form" class="search-pf has-button search">
      <div class="form-group">
        <button
          class="btn btn-primary btn-lg margin-left-md"
          type="button"
          v-on:click="updateAudits()"
        >{{$t('audit.update')}}</button>
        <button
          class="btn btn-danger btn-lg margin-left-md"
          type="button"
          data-toggle="modal"
          data-target="#deleteAllAuditModal"
        >{{$t('audit.delete')}}</button>
      </div>
    </form>

    <h3 v-if=" view.isLoaded">{{ $t('list') }}</h3>
    <vue-good-table
      v-show="view.isLoaded"
      :customRowsPerPageDropdown="[25,50,100]"
      :perPage="25"
      :columns="auditColumns"
      :rows="auditRows"
      :lineNumbers="false"
      :defaultSortBy="{field: 'when', type: 'asc'}"
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
          <span class="pficon pficon-screen span-right-icon"></span>
          {{props.row.ip}}
        </td>
        <td class="fancy">
          <span class="fa fa-share span-right-icon"></span>
          {{props.row.share}}
        </td>
        <td>
          <span class="fa fa-cog span-right-icon"></span>
          <b>{{props.row.op | uppercase}}</b>
          :
          <code>{{props.row.arg}}</code>
        </td>
      </template>
    </vue-good-table>

    <div class="modal" id="deleteAllAuditModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{$t('audit.delete_all_audits')}}</h4>
          </div>
          <form class="form-horizontal" v-on:submit.prevent="deleteAudits()">
            <div class="modal-body">
              <div class="form-group">
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('are_you_sure')}}?</label>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-default" type="button" data-dismiss="modal">{{$t('cancel')}}</button>
              <button class="btn btn-danger" type="submit">{{$t('delete')}}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
var moment = require("moment");

export default {
  name: "Mailboxes",
  mounted() {
    window.jQuery(".selectpicker").selectpicker();
    this.getAudits();
  },
  data() {
    return {
      view: {
        isLoaded: false
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
          label: this.$i18n.t("audit.address"),
          field: "ip",
          filterable: true,
          sortFn: function(a, b, col, rowX, rowY) {
            a = a.split(".");
            b = b.split(".");
            for (var i = 0; i < a.length; i++) {
              if ((a[i] = parseInt(a[i])) < (b[i] = parseInt(b[i]))) return -1;
              else if (a[i] > b[i]) return 1;
            }
          }
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
        }
      ],
      auditRows: [],
      filter: {
        username: "",
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
      }
    };
  },
  methods: {
    getAudits() {
      var context = this;

      context.view.isLoaded = false;
      nethserver.exec(
        ["nethserver-samba/audit/read"],
        {
          action: "query",
          username: context.filter.username,
          address: context.filter.address,
          share: context.filter.share,
          operation: context.filter.operation,
          message: context.filter.message,
          from: moment(context.filter.from).unix(),
          to: moment(context.filter.to).unix()
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }
          context.auditRows = success;

          context.view.isLoaded = true;
        },
        function(error) {
          console.error(error);
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
          context.getAudits();
        },
        function(error) {
          console.error(error);
        }
      );
    },
    deleteAudits() {
      var context = this;
      // notification
      nethserver.notifications.success = this.$i18n.t("audit.deleted_ok");
      nethserver.notifications.error = this.$i18n.t("audit.deleted_error");

      nethserver.exec(
        ["nethserver-samba/audit/delete"],
        {
          action: "delete",
          username: context.filter.username,
          address: context.filter.address,
          share: context.filter.share,
          operation: context.filter.operation,
          message: context.filter.message,
          from: moment(context.filter.from).unix(),
          to: moment(context.filter.to).unix()
        },
        function(stream) {
          console.info("deleted", stream);
        },
        function(success) {
          context.getAudits();
          $("#deleteAllAuditModal").modal("hide");
        },
        function(error) {
          console.error(error);
        }
      );
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