module.exports = {
    root: true,
    extends: [
        'plugin:@wordpress/eslint-plugin/recommended',
        'prettier',
    ],
    env: {
        browser: true,
        es2021: true,
        node: true,
        jquery: true,
    },
    parserOptions: {
        ecmaVersion: 'latest',
        sourceType: 'module',
    },
    globals: {
        wp: 'readonly',
        jQuery: 'readonly',
        pbjs: 'readonly',
        googletag: 'readonly',
        pubcontextConfig: 'readonly',
        wpAdAgentAdmin: 'readonly',
    },
    rules: {
        'no-console': 'warn',
        'no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
        'prefer-const': 'error',
        'no-var': 'error',
        'eqeqeq': ['error', 'always'],
        'curly': ['error', 'all'],
        '@wordpress/no-unsafe-wp-apis': 'warn',
    },
    overrides: [
        {
            files: ['webpack.config.js', '.eslintrc.js'],
            env: {
                node: true,
            },
            rules: {
                '@wordpress/no-unused-vars-before-return': 'off',
            },
        },
        {
            files: ['tests/**/*.js'],
            env: {
                jest: true,
            },
        },
    ],
    ignorePatterns: [
        'node_modules/',
        'vendor/',
        'dist/',
        'build/',
        '*.min.js',
    ],
};
