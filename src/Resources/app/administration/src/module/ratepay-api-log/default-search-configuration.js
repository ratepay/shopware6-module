const defaultSearchConfiguration = {
    _searchable: true,
    additionalData: {
        transactionId: {
            _searchable: true,
            _score: 100,
        },
        descriptor: {
            _searchable: true,
            _score: 100,
        },
        orderNumber: {
            _searchable: true,
            _score: 80,
        },
        firstname: {
            _searchable: true,
            _score: 60,
        },
        lastname: {
            _searchable: true,
            _score: 60,
        },
        mail: {
            _searchable: true,
            _score: 60,
        },
    },
    operation: {
        _searchable: true,
        _score: 80,
    },
    response: {
        _searchable: true,
        _score: 50,
    },
};

export default defaultSearchConfiguration;
