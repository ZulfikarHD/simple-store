<script setup lang="ts">
/**
 * AppLayout Component
 * Layout wrapper yang memilih layout yang tepat berdasarkan role user
 * Admin akan menggunakan AppSidebarLayout (dengan admin sidebar)
 * Customer akan menggunakan AppCustomerLayout (tanpa admin sidebar)
 *
 * @author Zulfikar Hidayatullah
 */
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import AppCustomerLayout from '@/layouts/app/AppCustomerLayout.vue'
import type { BreadcrumbItemType } from '@/types'

interface Props {
    breadcrumbs?: BreadcrumbItemType[]
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
})

const page = usePage()

/**
 * Computed untuk cek apakah user adalah admin
 */
const isAdmin = computed(() => {
    const user = (page.props as { auth?: { user?: { role?: string } } }).auth?.user
    return user?.role === 'admin'
})

/**
 * Select component berdasarkan role
 */
const LayoutComponent = computed(() => {
    return isAdmin.value ? AppSidebarLayout : AppCustomerLayout
})
</script>

<template>
    <component :is="LayoutComponent" :breadcrumbs="breadcrumbs">
        <slot />
    </component>
</template>
