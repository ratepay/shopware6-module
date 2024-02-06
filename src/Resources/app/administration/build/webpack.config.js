const { resolve } = require('path');
module.exports = () => {
    return {
        resolve: {
            alias: {
                'xml-formatter': resolve(__dirname, '..', 'node_modules', 'xml-formatter'),
                'highlight.js': resolve(__dirname, '..', 'node_modules', 'highlight.js'),
            }
        }
    };
}
