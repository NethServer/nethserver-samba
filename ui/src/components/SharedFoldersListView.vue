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
  <div class="list-group list-view-pf list-view-pf-view no-mg-top mg-top-10">
    <div v-for="item of items" v-bind:key="item.name" class="list-group-item">
      <div class="list-view-pf-actions">
        <button class="btn btn-default" v-on:click="$emit('item-edit', item)">
          <span class="fa fa-pencil"></span>
          {{ $t('sharedfolders.item_edit_button')}}
        </button>
        <div class="dropdown pull-right dropdown-kebab-pf">
          <button
            class="btn btn-link dropdown-toggle"
            type="button"
            v-bind:id="item.id + '-ddm'"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
          >
            <span class="fa fa-ellipsis-v"></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-right" v-bind:aria-labelledby="item.id + '-ddm'">
            <li>
              <a href="#" v-on:click="$emit('item-delete', item)">
                <span class="fa fa-times span-right-margin"></span>
                {{ $t('sharedfolders.item_delete_button') }}
              </a>
            </li>
          </ul>
        </div>
      </div>

      <div class="list-view-pf-main-info small-list">
        <div class="list-view-pf-left">
          <span
            class="fa list-view-pf-icon-sm fa-folder-open"
            v-bind:title="$t('sharedfolders.icon-tooltip-fa-folder-open')"
          ></span>
        </div>
        <div class="list-view-pf-body">
          <div class="list-view-pf-description">
            <div class="list-group-item-heading">{{ item.name }}</div>
            <div class="list-group-item-text">{{ item.Description }}</div>
          </div>
          <div class="list-view-pf-additional-info rules-info">
              <!-- no additional info -->
          </div>
        </div>
      </div>
    </div>
    <!-- end item -->
  </div>
  <!-- end list -->
</template>

<script>
export default {
  name: "SharedFoldersListView",
  props: {
    items: Array
  },
  data() {
    return {};
  },
  methods: {
  }
};
</script>

<style scoped>
.list-group-item-heading {
  width: calc(60% - 20px) !important;
}
.list-group-item-text {
  width: calc(40% - 40px) !important;
}
</style>
