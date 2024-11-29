<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import {Head} from '@inertiajs/vue3';

const props = defineProps({
    history: Array,
});
</script>

<template>
    <MainLayout>
        <Head title="История"/>
        <v-container>

            <v-table
                height="1500px"
                fixed-header
            >
                <thead>
                <tr>
                    <th class="text-left">
                        Дата и время
                    </th>
                    <th class="text-left">
                        Действие
                    </th>
                    <th class="text-left">
                        Результат
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="val in history"
                    :key="val.id">
                    <td>{{
                            new Date(val.created_at * 1000).toLocaleString("ru",
                                {
                                    second: 'numeric',
                                    year: 'numeric',
                                    month: 'long',
                                    weekday: 'long',
                                    hour: 'numeric',
                                    minute: 'numeric',

                                })
                        }}
                    </td>
                    <td>{{ val.entity_type }}</td>
                    <td> {{ val.type }}</td>
                </tr>
                </tbody>
            </v-table>

        </v-container>

    </MainLayout>
</template>
