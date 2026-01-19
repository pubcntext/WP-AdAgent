/**
 * Jest setup file for WP-AdAgent JavaScript tests.
 */

// Mock WordPress global objects
global.wp = {
    element: {
        createElement: jest.fn(),
        Fragment: jest.fn(),
    },
    blocks: {
        registerBlockType: jest.fn(),
    },
    components: {},
    i18n: {
        __: (text) => text,
        _x: (text) => text,
        _n: (single, plural, number) => (number === 1 ? single : plural),
        sprintf: (format, ...args) => {
            let i = 0;
            return format.replace(/%[sd]/g, () => args[i++]);
        },
    },
    blockEditor: {},
    data: {
        select: jest.fn(),
        dispatch: jest.fn(),
        useSelect: jest.fn(),
        useDispatch: jest.fn(),
    },
    apiFetch: jest.fn(),
};

// Mock jQuery
global.jQuery = jest.fn(() => ({
    ready: jest.fn(),
    on: jest.fn(),
    off: jest.fn(),
    trigger: jest.fn(),
    ajax: jest.fn(),
    val: jest.fn(),
    html: jest.fn(),
    text: jest.fn(),
    attr: jest.fn(),
    prop: jest.fn(),
    addClass: jest.fn(),
    removeClass: jest.fn(),
    show: jest.fn(),
    hide: jest.fn(),
    find: jest.fn(),
}));
global.$ = global.jQuery;

// Mock pubcontextConfig
global.pubcontextConfig = {
    apiEndpoint: 'https://api.pubcontext.com/match',
    semanticEnabled: true,
    prebidTimeout: 3000,
    placements: [],
};

// Mock wpAdAgentAdmin
global.wpAdAgentAdmin = {
    ajaxUrl: '/wp-admin/admin-ajax.php',
    nonce: 'test-nonce',
    restUrl: '/wp-json/pubcontext/v1/',
};

// Console mock to prevent noise in tests
global.console = {
    ...console,
    log: jest.fn(),
    warn: jest.fn(),
    error: jest.fn(),
};
