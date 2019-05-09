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
    <h1>{{$t('dashboard.title')}}</h1>

    <div class="row">
      <div class="col-sm-12">
        <h3>
          WORKGROUP:
          <b>{{workgroup || '-'}}</b>
        </h3>
      </div>
      <div class="col-sm-12">
        <h3>{{$t('dashboard.ibays')}}</h3>
        <div v-if="!view.isLoaded" class="spinner spinner-lg view-spinner"></div>

        <div v-if="view.isLoaded && ibays.length == 0" class="alert alert-info alert-dismissable">
          <span class="pficon pficon-info"></span>
          <strong>{{$t('info')}}:</strong>
          {{$t('no_data_found')}}.
        </div>
        <div
          v-if="view.isLoaded"
          class="list-group list-view-pf list-view-pf-view no-mg-top mg-top-10"
        >
          <div v-for="(m, mk) in ibays" v-bind:key="mk" class="list-group-item">
            <div class="list-view-pf-main-info small-list">
              <div class="list-view-pf-left">
                <span :class="['fa', 'list-view-pf-icon-sm', 'fa-inbox']"></span>
              </div>
              <div class="list-view-pf-body">
                <div class="list-view-pf-description">
                  <div class="list-group-item-heading">{{m.name}}</div>
                </div>
                <div class="list-view-pf-additional-info rules-info">
                  <div class="list-view-pf-additional-info-item">
                    <strong>{{$t('dashboard.audit')}}</strong>
                    <span :class="[m.audit == 'enabled' ? 'fa fa-check green' : 'fa fa-times red']"></span>
                  </div>
                  <div class="list-view-pf-additional-info-item">
                    <strong>{{m.files}}</strong>
                    {{$t('dashboard.files')}}
                  </div>
                  <div class="list-view-pf-additional-info-item">
                    <strong>{{ m.size | byteFormat }}</strong>
                    {{$t('dashboard.size')}}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-12">
        <h3>{{$t('dashboard.smb_status')}}</h3>
        <div v-if="!view.isLoadedStatus" class="spinner spinner-lg view-spinner"></div>

        <div
          v-if="view.isLoadedStatus && status.length == 0"
          class="alert alert-info alert-dismissable"
        >
          <span class="pficon pficon-info"></span>
          <strong>{{$t('info')}}:</strong>
          {{$t('no_data_found')}}.
        </div>
        <div
          v-if="view.isLoadedStatus"
          class="list-group list-view-pf list-view-pf-view no-mg-top mg-top-10"
        >
          <div v-for="(m, mk) in status" v-bind:key="mk" class="list-group-item">
            <div class="list-view-pf-main-info small-list">
              <div class="list-view-pf-left">
                <span :class="['fa', 'list-view-pf-icon-sm', 'fa-inbox']"></span>
              </div>
              <div class="list-view-pf-body">
                <div class="list-view-pf-description">
                  <div class="list-group-item-heading">
                    {{m.username}}:{{m.group}}
                    <br>
                    <span class="gray">({{m.name}})</span>
                  </div>
                  <div class="list-group-item-text">
                    {{$t('dashboard.machine')}}:
                    <span class="semi-bold">{{m.machine}}</span>
                  </div>
                </div>
                <div class="list-view-pf-additional-info rules-info">
                  <div class="list-view-pf-additional-info-item">
                    <span class="span-right-margin">{{$t('dashboard.path')}}:</span>
                    <code>{{m.path}}</code>
                  </div>
                  <div class="list-view-pf-additional-info-item">
                    <span class="span-right-margin">{{$t('dashboard.service')}}:</span>
                    <strong>{{m.service}}</strong>
                  </div>
                  <div class="list-view-pf-additional-info-item">
                    <span class="span-right-margin">{{$t('dashboard.protocol')}}:</span>
                    <strong>{{m.protocol}}</strong>
                  </div>
                  <div class="list-view-pf-additional-info-item">{{m.connected}}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Queue",
  mounted() {
    this.getIbays();
    this.getStatus();
  },
  data() {
    return {
      view: {
        isLoaded: false,
        isLoadedStatus: false
      },
      workgroup: "",
      ibays: [],
      status: []
    };
  },
  methods: {
    getIbays() {
      var context = this;

      context.view.isLoaded = false;
      nethserver.exec(
        ["nethserver-samba/dashboard/read"],
        {
          action: "ibays"
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }
          context.ibays = success.ibays;
          context.workgroup = success.workgroup;

          context.view.isLoaded = true;
        },
        function(error) {
          console.error(error);
        }
      );
    },
    getStatus() {
      var context = this;

      context.view.isLoadedStatus = false;
      nethserver.exec(
        ["nethserver-samba/dashboard/read"],
        {
          action: "smbstatus"
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }
          context.status = success;
          context.view.isLoadedStatus = true;
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
.no-mg-top {
  margin-top: 0px !important;
}

.mg-top-10 {
  margin-top: 10px !important;
}

.green {
  color: #3f9c35;
}

.red {
  color: #cc0000;
}

.gray {
  color: #72767b !important;
}

.semi-bold {
  font-weight: 600;
}

.list-view-pf .list-group-item-heading {
  width: calc(80% - 20px);
}

.list-view-pf .list-group-item-text {
  width: calc(20% - 40px);
}

.span-right-margin {
  margin-right: 5px;
}
</style>