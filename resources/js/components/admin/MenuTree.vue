<template>
    <div class="menu-tree">
        <div v-for="item in items" :key="item.id" class="menu-item">
            <div class="flex items-center justify-between p-4 border-b border-border hover:bg-muted/5">
                <div class="flex items-center gap-3 flex-1">
                    <div class="flex items-center gap-2 flex-1">
                        <span class="text-sm font-medium text-foreground">{{ item.title }}</span>
                        <span class="text-xs px-2 py-1 rounded bg-muted text-muted-foreground">
                            {{ item.type }}
                        </span>
                        <span v-if="item.url || item.slug" class="text-xs text-muted-foreground">
                            {{ item.url || (item.slug ? '/' + item.slug : '') }}
                        </span>
                        <span v-if="!item.is_active" class="text-xs px-2 py-1 rounded bg-red-500/10 text-red-500">
                            Неактивен
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="$emit('edit', item)"
                        class="px-3 py-1 text-xs bg-blue-500 hover:bg-blue-600 text-white rounded transition-colors"
                    >
                        Редактировать
                    </button>
                    <button
                        @click="$emit('delete', item)"
                        class="px-3 py-1 text-xs bg-red-500 hover:bg-red-600 text-white rounded transition-colors"
                    >
                        Удалить
                    </button>
                </div>
            </div>
            <div v-if="item.children && item.children.length > 0" class="ml-6 border-l border-border">
                <MenuTree
                    :items="item.children"
                    @edit="$emit('edit', $event)"
                    @delete="$emit('delete', $event)"
                    @refresh="$emit('refresh')"
                />
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'MenuTree',
    props: {
        items: {
            type: Array,
            required: true,
        },
    },
    emits: ['edit', 'delete', 'refresh'],
}
</script>

<style scoped>
.menu-item {
    position: relative;
}
</style>

