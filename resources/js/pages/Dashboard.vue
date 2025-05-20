<script setup>
import { Head,usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AdminCalendar from '@/components/home/AdminCalendar.vue';
import { useToast } from 'vue-toastification'

defineProps({
    bookings: Array
});

const toast = useToast();
const page = usePage();
onMounted(() => {
    if  (page.props.flash && page.props.flash.success) {
        toast.success(page.props.flash.success);
    }
    if (page.props.flash && page.props.flash.error) {
        toast.error(page.props.flash.error);
    }
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                Appointment Calendar
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <AdminCalendar :bookings="bookings" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
