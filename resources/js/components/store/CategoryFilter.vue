<script setup lang="ts">
/**
 * CategoryFilter Component
 * Menampilkan filter kategori dalam bentuk tabs horizontal
 * untuk memfilter produk berdasarkan kategori
 */

/**
 * Props definition untuk CategoryFilter
 */
interface Category {
    id: number
    name: string
    slug: string
    products_count?: number
}

interface Props {
    categories: Category[]
    /** ID kategori yang sedang aktif, null untuk semua */
    activeCategory?: number | null
}

const props = withDefaults(defineProps<Props>(), {
    activeCategory: null,
})

/**
 * Emit event ketika kategori dipilih
 */
const emit = defineEmits<{
    select: [categoryId: number | null]
}>()

/**
 * Check apakah kategori sedang aktif
 */
function isActive(categoryId: number | null): boolean {
    return props.activeCategory === categoryId
}
</script>

<template>
    <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
        <!-- All Categories Button -->
        <button
            type="button"
            class="shrink-0 rounded-full px-4 py-2 text-sm font-medium transition-all"
            :class="
                isActive(null)
                    ? 'bg-primary text-primary-foreground'
                    : 'bg-secondary text-secondary-foreground hover:bg-secondary/80'
            "
            @click="emit('select', null)"
        >
            Semua
        </button>

        <!-- Category Buttons -->
        <button
            v-for="category in categories"
            :key="category.id"
            type="button"
            class="shrink-0 rounded-full px-4 py-2 text-sm font-medium transition-all"
            :class="
                isActive(category.id)
                    ? 'bg-primary text-primary-foreground'
                    : 'bg-secondary text-secondary-foreground hover:bg-secondary/80'
            "
            @click="emit('select', category.id)"
        >
            {{ category.name }}
            <span
                v-if="category.products_count !== undefined"
                class="ml-1 opacity-70"
            >
                ({{ category.products_count }})
            </span>
        </button>
    </div>
</template>

<style scoped>
/* Hide scrollbar but keep functionality */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>

