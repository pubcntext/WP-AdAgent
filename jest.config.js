module.exports = {
    testEnvironment: 'jsdom',
    testMatch: ['**/tests/js/**/*.test.js'],
    moduleFileExtensions: ['js', 'json'],
    collectCoverageFrom: [
        'assets/js/**/*.js',
        '!assets/js/**/*.min.js',
        '!**/node_modules/**',
    ],
    coverageDirectory: 'coverage/js',
    coverageReporters: ['text', 'lcov', 'html'],
    transform: {
        '^.+\\.js$': 'babel-jest',
    },
    moduleNameMapper: {
        '\\.(css|less|scss|sass)$': '<rootDir>/tests/js/__mocks__/styleMock.js',
    },
    setupFilesAfterEnv: ['<rootDir>/tests/js/setup.js'],
    globals: {
        wp: {},
        jQuery: {},
        pubcontextConfig: {},
    },
};
