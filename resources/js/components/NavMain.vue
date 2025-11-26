<script setup lang="ts">
/**
 * NavMain Component
 * Navigation utama sidebar dengan support untuk badge notifications
 * digunakan untuk menampilkan jumlah pesanan pending pada menu Pesanan
 *
 * @author Zulfikar Hidayatullah
 */
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuBadge,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="urlIsActive(item.href, page.url)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href" class="relative">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                        <!-- Badge untuk notifikasi -->
                        <SidebarMenuBadge
                            v-if="item.badge && item.badge > 0"
                            class="bg-primary text-primary-foreground"
                        >
                            {{ item.badge > 99 ? '99+' : item.badge }}
                        </SidebarMenuBadge>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
