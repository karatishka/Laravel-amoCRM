<template>
    <v-layout class="rounded rounded-md">
        <v-app-bar title="Application"></v-app-bar>

        <v-navigation-drawer>
            <v-list-item link title="Выбор Сделки" :href="route('deal')"></v-list-item>
            <v-list-item link title="История (Лог)" :href="route('history')"></v-list-item>
        </v-navigation-drawer>

        <v-main class="d-flex align-center justify-center" style="min-height: 300px;">
            <slot/>
        </v-main>
    </v-layout>
</template>
