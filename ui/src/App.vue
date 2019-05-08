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
  <div id="app">
    <nav
      id="navbar-left"
      class="nav-pf-vertical nav-pf-vertical-with-sub-menus nav-pf-persistent-secondary panel-group"
    >
      <ul class="list-group panel">
        <li
          id="dashboard-item"
          v-bind:class="[getCurrentPath('dashboard') ? 'active' : '', 'list-group-item']"
        >
          <a href="#/dashboard">
            <span class="fa fa-cube"></span>
            <span class="list-group-item-value">{{$t('dashboard.menu_title')}}</span>
          </a>
        </li>
        <li class="li-empty"></li>
        <li
          id="sharedfolders-item"
          v-bind:class="[getCurrentPath('sharedfolders') ? 'active' : '', 'list-group-item']"
        >
          <a href="#/sharedfolders">
            <span class="fa fa-folder-o"></span>
            <span class="list-group-item-value">{{$t('sharedfolders.menu_title')}}</span>
          </a>
        </li>
        <li
          id="audit-item"
          v-bind:class="[getCurrentPath('audit') ? 'active' : '', 'list-group-item']"
        >
          <a href="#/audit">
            <span class="fa fa-list"></span>
            <span class="list-group-item-value">{{$t('audit.menu_title')}}</span>
          </a>
        </li>
        <li
          v-bind:class="[getCurrentPath('settings') ? 'active' : '', 'list-group-item']"
          class="list-group-item"
        >
          <a href="#/settings">
            <span class="fa fa-cog"></span>
            <span class="list-group-item-value">{{$t('settings.menu_title')}}</span>
          </a>
        </li>
        <li
          id="logs-item"
          v-bind:class="[getCurrentPath('logs') ? 'active' : '', 'list-group-item']"
        >
          <a href="#/logs">
            <span class="fa fa-list"></span>
            <span class="list-group-item-value">{{$t('logs.title')}}</span>
          </a>
        </li>
        <li class="li-empty"></li>
        <li
          id="about-item"
          v-bind:class="[getCurrentPath('about') ? 'active' : '', 'list-group-item']"
        >
          <a href="#/about">
            <span class="fa fa-info"></span>
            <span class="list-group-item-value">{{$t('about.title')}}</span>
          </a>
        </li>
      </ul>
    </nav>
    <div class="container-fluid container-cards-pf">
      <router-view/>
    </div>
  </div>
</template>

<script>
export default {
  name: "App",
  methods: {
    getCurrentPath(route, offset) {
      if (offset) {
        return this.$route.path.split("/")[offset] === route;
      } else {
        return this.$route.path.split("/")[1] === route;
      }
    }
  }
};
</script>

<style>
</style>
