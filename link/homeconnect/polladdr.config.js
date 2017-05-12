{
    "Security": {
        "RequiredPoints": [7]
    },

    "DataSources": [
        {
            "Type": "RequestParams",
            "Source": "POST",
            "Properties": {
                "Parameters": [
                    "ID"
                ]
            }
        },
        {
            "Type": "Database",
            "Properties": {
                "Database": "useraccess",
                "Query": "SELECT IP, Port, LastUpdate FROM HomeClients WHERE HomeClients.ID = ?"
            }
        }
    ]
}
