{
    "Security": {
        "RequiredPoints": []
    },

    "DataGroups": [
        "Result"
    ],

    "DataSources": [
        {
            "Type": "Database",
            "Properties": {
                "Database": "useraccess",
                "Query": "SELECT * FROM SecurityPoint"
            },
            "Result": {
                "DataGroup": "Result",
                "NameInGroup": "QueryResult"
            }
        }
    ],

    "Result": {
        "DataGroup": "Result",
        "NameInGroup": "QueryResult"
    }
}
