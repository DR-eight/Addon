<template>
    <div>
        <div class="flex items-center">
            <div class="little-dot mr-1" :class="littleDotColour" />
            <span>
                {{ statusName }}
            </span>
        </div>
    </div>
</template>

<script>
export default {
    mixins: [IndexFieldtype],

    computed: {
        littleDotColour() {
            switch (this.value) {
                case 'cart':
                    return 'bg-grey-40'

                case 'placed':
                    return 'bg-orange'

                case 'dispatched':
                    return 'bg-blue-300'

                case 'cancelled':
                    return 'bg-red-500'

                default:
                    return ''
            }
        },

        statusName() {
            return __((
                this.value.charAt(0).toUpperCase() +
                this.value.substr(1).toLowerCase()
            ))
        },
    },
}
</script>
