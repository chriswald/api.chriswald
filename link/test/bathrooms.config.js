{
    "Security": {
        "RequiredPoints": []
    },

    "HttpMethod": "POST",

    "RequestParameters": [
        "ID"
    ],

    "DataGroups": [
        "Result"
    ],

    "DataSources": [
        {
            "Type": "Database",
            "Parameters": [
                {
                    "Source": "RequestParameters",
                    "SourceParameterName": "ID",
                    "DestinationParameterName": "ID"
                }
            ],
            "Properties": {
                "Database": "bathroomstatus",
                "Query": "SELECT Name FROM Bathroom WHERE Bathroom.ID = ?"
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
