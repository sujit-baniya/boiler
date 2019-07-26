<template>
    <div class="px-4 py-4 bg-white border border-b-0 shadow-lg" role="tabpanel"
         v-show="isActive"
         :id="id" :aria-labelledby="`${id}-tab`"
    >
        <slot></slot>
    </div>
</template>

<script>
    let uid = 0;

    export default {
        data() {
            return {
                id: `${this.$parent.id}-${uid++}-panel`,
                isActive: this.active
            };
        },

        props: {
            active: {
                type: Boolean
            },
            title: {
                type: String,
                required: true
            }
        }
    };
</script>
