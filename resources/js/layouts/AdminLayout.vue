<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link href="/admin" class="text-lg font-semibold text-gray-900 bg-gray-100 p-2 rounded-2xl hover:bg-gray-300">
                            Admin Dashboard
                        </Link>
                    </div>
                    <div class="flex items-center">
                        <a href="/auth/google/connect" class="text-lg font-semibold text-gray-900 bg-gray-100 p-2 rounded-2xl hover:bg-gray-300">
                            Connect Google Account
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <slot />
        </main>
    </div>
</template>
