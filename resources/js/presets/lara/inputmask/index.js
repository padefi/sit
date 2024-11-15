export default {
    root: ({ context, props, parent }) => ({
        class: [
            // Font
            'font-sans leading-none',

            // Borders
            'border-0 border-b appearance-none',
            'focus:outline-none focus:ring-0 peer',

            // Spacing
            'm-0 p-3',
            {
                'px-4 py-4': props.size == 'large',
                'px-2 py-2': props.size == 'small',
                'p-3': props.size == null
            },

            // Colors
            'text-surface-600 dark:text-surface-200',
            'placeholder:text-surface-400 dark:placeholder:text-surface-500',
            'bg-surface-0 dark:bg-surface-900',
            'border',
            { 'border-surface-300 dark:border-surface-600': !props.invalid },

            // Invalid State
            { 'border-red-500 dark:border-red-400': props.invalid },

            // States
            {
                'hover:border-primary-500 dark:hover:border-primary-400': !context.disabled && !props.invalid,
                'focus:border-gray-500 dark:focus:border-primary-400': !context.disabled && !props.invalid,
                'focus:border-red-500 dark:focus:border-red-400': props.invalid,
                'focus:outline-none focus:outline-offset-0 focus:ring focus:ring-primary-500/50 dark:focus:ring-primary-400/50': !context.disabled,
                'opacity-60 select-none pointer-events-none cursor-default': context.disabled
            },

            // Filled State *for FloatLabel
            { filled: parent.instance?.$name == 'FloatLabel' && context.filled },

            // Misc
            // 'rounded-md',
            'appearance-none',
            'transition-colors duration-200'
        ]
    })
};
