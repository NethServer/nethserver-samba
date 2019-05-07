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
        <h1>{{ $t('sharedfolders.title') }}</h1>
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
          ></shared-folders-list-view>
        </div>

        <shared-folder-edit-modal
          id="modalCreateSharedFolder"
          action="create"
          v-on:modal-close="read($event)"
          v-bind:item="currentItem"
        ></shared-folder-edit-modal>

        <shared-folder-edit-modal
          id="modalEditSharedFolder"
          action="edit"
          v-on:modal-close="read"
          v-bind:item="currentItem"
        ></shared-folder-edit-modal>

        <shared-folder-edit-modal
          id="modalDeleteSharedFolder"
          action="delete"
          v-on:modal-close="read"
          v-bind:item="currentItem"
        ></shared-folder-edit-modal>

    </div>
</template>

<script>
import execp from "@/execp"
import SharedFoldersListView from "@/components/SharedFoldersListView.vue";
import SharedFolderEditModal from "@/components/SharedFolderEditModal.vue";

export default {
    name: "SharedFolders",
    components: {
        SharedFoldersListView,
        SharedFolderEditModal,
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
        accountsprovider: "",
        sharedfolders: [],
        currentItem: {},
      };
    },
    methods: {
        createSharedFolder() {
            return {
                name: "",
                Description: "",
            }
        },
        openModal(id, item) {
          this.currentItem = item;
          window.jQuery("#" + id).modal();
        },
        read(eventData = {}) {
          this.vReadStatus = "running";
          execp("nethserver-samba/sharedfolders/read", {"action":"list"})
            .then(result => {
              for (let k in result) {
                if (result.hasOwnProperty(k)) {
                  this[k] = result[k];
                }
              }
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
            })
        },
    },
}
</script>