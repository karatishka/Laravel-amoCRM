<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import {Head} from '@inertiajs/vue3';

const props = defineProps({
    leads: Array,
});

</script>

<template>
    <MainLayout>
        <Head title="Выбор сделки"/>
        <v-container>

            <v-table
                height="300px"
                fixed-header
            >
                <thead>
                <tr>
                    <th class="text-left">
                        ID
                    </th>
                    <th class="text-left">
                        Название
                    </th>
                    <th class="text-left">
                        Дата создания
                    </th>
                    <th class="text-left">
                        контакт
                    </th>
                    <th class="text-left">
                        Действия
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr
                    v-for="lead in leads"
                    :key="lead.id"
                >
                    <td>{{ lead.id }}</td>
                    <td>{{ lead.name }}</td>
                    <td>{{
                            new Date(lead.created_at * 1000).toLocaleString("ru",
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
                    <td>{{ lead.contacts ? 'ДА' : 'НЕТ' }}</td>
                    <td>
                        <v-btn class="text-none text-subtitle-1"
                               color="#5865f2"
                               size="small"
                               variant="outlined"
                               :disabled="lead.contacts"
                               :href="route('contact', {id: lead.id} )"
                        > Привязать контакт
                        </v-btn>
                    </td>
                </tr>
                </tbody>
            </v-table>

        </v-container>

    </MainLayout>
</template>

