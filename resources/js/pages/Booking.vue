<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
// import BookingForm from '@/components/home/BookingForm.vue';
import BookingForm2 from '@/components/home/BookingForm2.vue';
</script>

<template>
    <Head title="Book Appointment">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-gray-50 p-4">
        <div class=" mx-auto bg-white p-8 rounded-lg shadow-md text-gray-400">
        <h1 class="text-2xl font-bold mb-6 text-center">Book an Appointment</h1>
        <BookingForm2/>
        </div>
    </div>
</template>
