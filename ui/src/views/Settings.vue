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
<template>
  <div>
    <h1>{{ $t('settings.title') }}</h1>
    <div v-if="!view.isLoaded" class="spinner spinner-lg"></div>
    <div v-show="view.isLoaded">
      <form class="form-horizontal" v-on:submit.prevent="saveSettings()">
        <div :class="['form-group', errors.Workgroup.hasError ? 'has-error' : '']">
          <label
            class="col-sm-2 control-label"
            for="textInput-modal-markup"
          >{{$t('settings.workgroup')}}</label>
          <div class="col-sm-5">
            <input
              :disabled="!settings.WorkgroupEditable"
              type="text"
              v-model="settings.Workgroup"
              class="form-control"
            >
            <span v-if="errors.Workgroup.hasError" class="help-block">
              {{$t('validation.validation_failed')}}:
              {{$t('validation.'+errors.Workgroup.message)}}
            </span>
          </div>
        </div>

        <div v-show="!settings.WorkgroupEditable" :class="['form-group', errors.InheritOwner.hasError ? 'has-error' : '']">
          <label
            class="col-sm-2 control-label"
            for="textInput-modal-markup"
          >{{$t('settings.inherit_owner')}}</label>
          <div class="col-sm-5">
            <select v-model="settings.InheritOwner" class="form-control selectpicker">
              <option value="yes">{{$t('settings.yes_inherit')}}</option>
              <option value="no">{{$t('settings.no_inherit')}}</option>
            </select>
            <span v-if="errors.InheritOwner.hasError" class="help-block">
              {{$t('validation.validation_failed')}}:
              {{$t('validation.'+errors.InheritOwner.message)}}
            </span>
          </div>
        </div>

        <div v-if="!settings.WorkgroupEditable" :class="['form-group', errors.HomeAdmStatus.hasError ? 'has-error' : '']">
          <label
            class="col-sm-2 control-label"
            for="textInput-modal-markup"
          >{{$t('settings.grant_home_dir')}}</label>
          <div class="col-sm-5">
            <input type="checkbox" v-model="settings.HomeAdmStatus" class="form-control">
            <span v-if="errors.HomeAdmStatus.hasError" class="help-block">
              {{$t('validation.validation_failed')}}:
              {{$t('validation.'+errors.HomeAdmStatus.message)}}
            </span>
          </div>
        </div>

        <div v-if="!settings.WorkgroupEditable" :class="['form-group', errors.ShareAdmStatus.hasError ? 'has-error' : '']">
          <label
            class="col-sm-2 control-label"
            for="textInput-modal-markup"
          >{{$t('settings.grant_share_dir')}}</label>
          <div class="col-sm-5">
            <input type="checkbox" v-model="settings.ShareAdmStatus" class="form-control">
            <span v-if="errors.ShareAdmStatus.hasError" class="help-block">
              {{$t('validation.validation_failed')}}:
              {{$t('validation.'+errors.ShareAdmStatus.message)}}
            </span>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label" for="textInput-modal-markup">
            <div
              v-if="view.isSaving"
              class="spinner spinner-sm form-spinner-loader adjust-top-loader"
            ></div>
          </label>
          <div class="col-sm-5">
            <button class="btn btn-primary" type="submit">{{$t('save')}}</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: "Settings",
  mounted() {
    window.jQuery(".selectpicker").selectpicker();
    this.getSettings();
  },
  data() {
    return {
      view: {
        isLoaded: false,
        isSaving: false
      },
      settings: {
        InheritOwner: "no",
        WorkgroupEditable: false,
        Workgroup: "LOCAL",
        HomeAdmStatus: "disabled",
        ShareAdmStatus: "disabled"
      },
      errors: this.initErrors()
    };
  },
  methods: {
    initErrors() {
      return {
        InheritOwner: {
          hasError: false,
          message: ""
        },
        Workgroup: {
          hasError: false,
          message: ""
        },
        HomeAdmStatus: {
          hasError: false,
          message: ""
        },
        ShareAdmStatus: {
          hasError: false,
          message: ""
        }
      };
    },
    getSettings() {
      var context = this;

      context.view.isLoaded = false;
      nethserver.exec(
        ["nethserver-samba/settings/read"],
        {},
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }
          context.settings.HomeAdmStatus = success.HomeAdmStatus == "enabled";
          context.settings.ShareAdmStatus = success.ShareAdmStatus == "enabled";
          context.settings.InheritOwner = success.InheritOwner;

          context.settings.Workgroup = success.Workgroup;
          context.settings.WorkgroupEditable = success.WorkgroupEditable;

          context.view.isLoaded = true;
        },
        function(error) {
          console.error(error);
        }
      );
    },
    saveSettings() {
      var context = this;
      var settingsObj = {
        InheritOwner: context.settings.InheritOwner,
        WorkgroupEditable: context.settings.WorkgroupEditable,
        Workgroup: context.settings.Workgroup,
        HomeAdmStatus: context.settings.HomeAdmStatus ? "enabled" : "disabled",
        ShareAdmStatus: context.settings.ShareAdmStatus ? "enabled" : "disabled"
      };

      context.view.isSaving = true;
      context.errors = context.initErrors();
      nethserver.exec(
        ["nethserver-samba/settings/validate"],
        settingsObj,
        null,
        function(success) {
          context.view.isSaving = false;

          // notification
          nethserver.notifications.success = context.$i18n.t(
            "settings.settings_updated_ok"
          );
          nethserver.notifications.error = context.$i18n.t(
            "settings.settings_updated_error"
          );

          // update values
          nethserver.exec(
            ["nethserver-samba/settings/update"],
            settingsObj,
            function(stream) {
              console.info("settings", stream);
            },
            function(success) {
              context.getSettings();
            },
            function(error, data) {
              console.error(error, data);
            }
          );
        },
        function(error, data) {
          var errorData = {};
          context.view.isSaving = false;
          context.errors = context.initErrors();

          try {
            errorData = JSON.parse(data);
            for (var e in errorData.attributes) {
              var attr = errorData.attributes[e];
              context.errors[attr.parameter].hasError = true;
              context.errors[attr.parameter].message = attr.error;
            }
          } catch (e) {
            console.error(e);
          }
        }
      );
    }
  }
};
</script>

<style>
.adjust-top-loader {
  margin-top: -4px;
}
</style>
