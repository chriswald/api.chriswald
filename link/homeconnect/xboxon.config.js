{
    "Security": {
        "RequiredPoints": [6]
    },

    "HttpMethod": "POST",

    "RequestParameters": [
        "Address",
        "LiveID"
    ],

    "DataGroups": [
        "Result"
    ],

    "DataSources": [
        {
            "Type": "Script",
            "Parameters": [
                {
                    "Source": "RequestParameters",
                    "SourceParameterName": "Address",
                    "DestinationParameterName": "Address"
                },
                {
                    "Source": "RequestParameters",
                    "SourceParameterName": "LiveID",
                    "DestinationParameterName": "LiveID"
                }
            ],
            "Properties": {
                "File": "homeconnect/scripts/xboxon.php",
                "Entrypoint": "ExecInit"
            },
            "Result": {
                "DataGroup": "Result",
                "NameInGroup": "ScriptResult"
            }
        }
    ],

    "Result": {
        "DataGroup": "Result",
        "NameInGroup": "ScriptResult"
    }
}
