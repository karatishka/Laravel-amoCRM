<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
import {Head} from '@inertiajs/vue3';
import {useField, useForm} from 'vee-validate'
import axios from "axios";
import {ref} from "vue";

const props = defineProps({
    id: String,
});

const success = ref(false);

const {handleSubmit, handleReset} = useForm({
    validationSchema: {
        name(value) {
            if (value?.length >= 2) return true

            return 'Имя должно содержать не менее 2 символов.'
        },
        phone(value) {
            if (value?.length >= 7) return true
            return 'Номер телефона должен содержать не менее 7 цифр.'
        },
        comment(value) {
            if (value?.length >= 2) return true
            return 'Не менее 2 символов.'
        },
    },
})

const name = useField('name')
const phone = useField('phone')
const comment = useField('comment')

const submit = handleSubmit(async values => {
    try {
        await axios.post(route('contact.store', {id: props.id}), values);
        success.value = true;
        handleReset();
    } catch (err) {
        alert(err.message);
    }
})

</script>

<template>
    <MainLayout>
        <Head title="Контакт"/>
        <v-container>
            <v-row v-if="success">
                <v-col cols="4">
                    <v-alert color="success"
                             text="Успешно добавлен"></v-alert>
                </v-col>
            </v-row>

            <v-row start>
                <v-col cols="4">
                    <form @submit.prevent="submit">
                        <v-text-field
                            v-model="name.value.value"
                            :error-messages="name.errorMessage.value"
                            label="Имя"
                        ></v-text-field>

                        <v-text-field
                            v-model="phone.value.value"
                            :counter="7"
                            :error-messages="phone.errorMessage.value"
                            label="Телефон"
                        ></v-text-field>

                        <v-textarea
                            v-model="comment.value.value"
                            :error-messages="comment.errorMessage.value"
                            label="Комментарий"
                        ></v-textarea>

                        <v-btn
                            class="me-4"
                            type="submit"
                        >
                            Привязать
                        </v-btn>

                        <v-btn @click="handleReset">
                            Очистить
                        </v-btn>
                    </form>
                </v-col>
            </v-row>
        </v-container>
    </MainLayout>
</template>

