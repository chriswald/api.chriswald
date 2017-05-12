{
    "Security": {
        "RequiredPoints": []
    },

    "DataSources": [
        {
            "Type": "RequestParams",
            "Properties": {
                "Parameters": [
                    "ID"
                ]
            }
        },
        {
            "Type": "Database",
            "Properties": {
                "Database": "bathroomstatus",
                "Query": "SELECT Name FROM Bathroom WHERE Bathroom.ID = ?"
            }
        }
    ]
}
