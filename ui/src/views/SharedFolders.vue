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

<style scoped>
.spaced {
  margin-top: 1em;
}
</style>


<template>
  <div>
    <div v-if="vReadStatus == 'success' && sharedfolders.length == 0" class="blank-slate-pf">
      <div class="blank-slate-pf-icon">
        <span class="fa fa-folder-open"></span>
      </div>
      <h2>{{ $t('sharedfolders.title') }}</h2>
      <p>{{ $t("sharedfolders.empty_state_message") }}</p>
      <p>
        <doc-info
          place="docinfo"
          :placement="'top'"
          :title="$t('docs.sharedfolders')"
          :chapter="'shared_folder'"
          :section="'shared-folders'"
          :inline="false"
          :lang="'en'"
        ></doc-info>
      </p>
      <div class="blank-slate-pf-main-action">
        <button
          class="btn btn-primary btn-lg"
          v-on:click="openModal('modalCreateSharedFolder', createSharedFolder())"
        >{{ $t('sharedfolders.empty_state_button') }}</button>
      </div>
    </div>
    <div v-else>
      <h1>{{ $t('sharedfolders.title') }}</h1>
      <h4 v-if="lastDate != null" class="right gray">
        {{$t('sharedfolders.update_at')}}:
        <b v-if="lastDate > 0">{{lastDate | dateFormat}}</b>
        <b v-if="lastDate == 0">{{$t('sharedfolders.not_available')}}</b>
      </h4>
      <doc-info
        :placement="'top'"
        :title="$t('docs.sharedfolders')"
        :chapter="'shared_folder'"
        :section="'shared-folders'"
        :inline="false"
        :lang="'en'"
      ></doc-info>
      <div v-if="vReadStatus == 'running'" class="spinner spinner-lg view-spinner"></div>
      <div v-else-if="vReadStatus == 'error'">
        <div class="alert alert-danger">
          <span class="pficon pficon-error-circle-o"></span>
          <strong>OOOPS!</strong> An unexpected error has occurred:
          <pre>{{ vReadError }}</pre>
        </div>
      </div>
      <div v-else class="spaced">
        <h3>{{$t('actions_title')}}</h3>
        <button
          class="btn btn-primary btn-lg"
          v-on:click="openModal('modalCreateSharedFolder', createSharedFolder())"
        >{{ $t('sharedfolders.create_sharedfolder_button') }}</button>

        <h3>{{$t('list_title')}}</h3>
        <shared-folders-list-view
          v-bind:items="sharedfolders"
          v-on:modal-close="read"
          v-on:item-edit="openModal('modalEditSharedFolder', $event)"
          v-on:item-delete="openModal('modalDeleteSharedFolder', $event)"
          v-on:item-reset-permissions="openModal('modalResetPermissionsSharedFolder', $event)"
          v-on:item-migrate="openModal('modalMigrateSharedFolder', $event)"
        ></shared-folders-list-view>
      </div>
    </div>

    <shared-folder-edit-modal
      id="modalCreateSharedFolder"
      action="create"
      v-on:modal-close="read($event)"
      v-bind:initial-item="currentItem"
      v-bind:groups-list="groups"
      v-bind:accounts-provider="accountsProvider"
    ></shared-folder-edit-modal>

    <shared-folder-edit-modal
      id="modalEditSharedFolder"
      action="edit"
      v-on:modal-close="read"
      v-bind:initial-item="currentItem"
      v-bind:groups-list="groups"
      v-bind:accounts-provider="accountsProvider"
    ></shared-folder-edit-modal>

    <shared-folder-edit-modal
      id="modalDeleteSharedFolder"
      action="delete"
      v-on:modal-close="read"
      v-bind:initial-item="currentItem"
    ></shared-folder-edit-modal>

    <shared-folder-edit-modal
      id="modalResetPermissionsSharedFolder"
      action="reset-permissions"
      v-on:modal-close="read"
      v-bind:initial-item="currentItem"
    ></shared-folder-edit-modal>

    <shared-folder-edit-modal
      id="modalMigrateSharedFolder"
      action="migrate"
      v-on:modal-close="read"
      v-bind:initial-item="currentItem"
    ></shared-folder-edit-modal>
  </div>
</template>

<script>
import execp from "@/execp";
import SharedFoldersListView from "@/components/SharedFoldersListView.vue";
import SharedFolderEditModal from "@/components/SharedFolderEditModal.vue";

export default {
  name: "SharedFolders",
  components: {
    SharedFoldersListView,
    SharedFolderEditModal
  },
  beforeRouteLeave(to, from, next) {
    $(".modal").modal("hide");
    next();
  },
  mounted() {
    this.read();
  },
  data() {
    return {
      vReadStatus: "running",
      vReadError: "",
      accountsProvider: "",
      sharedfolders: [],
      currentItem: {},
      groups: [],
      lastDate: null
    };
  },
  methods: {
    createSharedFolder() {
      return {
        Description: "",
        OwningGroup: "",
        SmbRecycleBinStatus: "disabled",
        SmbRecycleBinVersionsStatus: "disabled",
        SmbShareBrowseable: "enabled",
        acls: { EVERYONE: this.accountsProvider == "ad" ? "" : "rw" },
        name: "",
        guestAccess: "disabled",
        SmbAuditStatus: "disabled"
      };
    },
    openModal(id, item) {
      this.currentItem = Object.assign(this.createSharedFolder(), item);
      window.jQuery("#" + id).modal();
    },
    read(eventData = {}) {
      this.vReadStatus = "running";
      execp("nethserver-samba/sharedfolders/read", { action: "list" })
        .then(result => {
          this.sharedfolders = result.sharedfolders;
          this.accountsProvider = result.accountsProvider;
          this.groups = result.groups;
          this.lastDate = result.date;
          this.vReadStatus = "success";

          this.$nextTick(function() {
            $("[data-toggle=popover]")
              .popovers()
              .on("hidden.bs.popover", function(e) {
                $(e.target).data("bs.popover").inState.click = false;
              });
          });
        })
        .catch(error => {
          this.vReadStatus = "error";
          this.vReadError = error;
        });
    }
  }
};
</script>