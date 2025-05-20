<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

</script>

<template>
    <Head title="Connect Calendar" />
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Connect Your Google Calendar
            </h2>
        </template>
        <div class="py-12 text-gray-800 ">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h1 class="text-2xl font-bold mb-6">Appointment Calendar</h1>
                    <p class="mb-4">Connect your Google Calendar to manage your appointments.</p>
                    <a
                        href="/auth/google/connect"
                        class="inline-block px-3 py-1 text-sm rounded bg-blue-600 text-white hover:bg-blue-700"
                    >
                        Connect Google Account
                    </a>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
