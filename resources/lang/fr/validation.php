<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [

        'nom' => [
            'required' => 'Le champ nom est requis.',
            'string' => 'Le champ nom doit être une chaîne de caractères.',
            'max' => 'Le champ nom ne peut pas dépasser :max caractères.',
        ],

        'telephone' => [
            'required' => 'Le champ téléphone est requis.',
            'integer' => 'Le champ téléphone doit être un nombre entier.',
        ],

        'type' => [
            'required' => 'Le champ type est requis.',
            'in' => 'Le champ type doit être parmi :values.',
        ],

        'email' => [
            'required' => 'Le champ email est requis.',
            'string' => 'Le champ email doit être une chaîne de caractères.',
            'email' => 'Le champ email doit être une adresse email valide.',
            'max' => 'Le champ email ne peut pas dépasser :max caractères.',
            'unique' => 'L\'adresse email est déjà utilisée.',
        ],

        'password' => [
            'required' => 'Le champ mot de passe est requis.',
        ],
        
        'confirmPassword' => [
            'required' => 'Le champ confirmation de mot de passe est requis.',
            'same' => 'Le champ confirmation de mot de passe doit être identique au champ mot de passe.',
        ],


        'code' => [
            'exists' => 'Le code sélectionné est invalide.',
        ],

        'current_password' => [
            'required' => 'Le champ du mot de passe actuel est requis.',
        ],

        'new_password' => [
            'required' => 'Le champ du nouveau mot de passe est requis.',
        ],

        'required' => 'Le champ :attribute est requis.',
        'string' => 'Le champ :attribute doit être une chaîne de caractères.',
        'numeric' => 'Le champ :attribute doit être un nombre.',
        'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
        'image' => 'Le champ :attribute doit être une image.',
        'mimes' => 'Le champ :attribute doit être un fichier de type :values.',
        'max' => [
            'numeric' => 'Le champ :attribute ne doit pas dépasser :max.',
            'file' => 'Le champ :attribute ne doit pas dépasser :max kilo-octets.',
        ],
        'array' => 'Le champ :attribute doit être un tableau.',
        'min' => [
            'numeric' => 'Le champ :attribute doit avoir au moins :min.',
            'file' => 'Le champ :attribute doit avoir au moins :min kilo-octets.',
            'string' => 'Le champ :attribute doit avoir au moins :min caractères.',
            'array' => 'Le champ :attribute doit avoir au moins :min éléments.',
        ],
        'categorie_id.exists' => 'La catégorie sélectionnée est invalide.',
        'images.required' => 'Au moins une image doit être fournie.',
        'images.array' => 'Le champ images doit être un tableau.',
        'images.min' => 'Au moins une image doit être fournie.',
        'images.*.image' => 'Les fichiers images doivent être des images valides.',
        'images.*.file' => 'Les fichiers images doivent être des fichiers valides.',
        'images.*.mimes' => 'Les fichiers images doivent être de type :values.',
        'images.*.max' => 'Les fichiers images ne doivent pas dépasser :max kilo-octets.',


        'idTransaction' => [
            "required"=> "L'ID de transaction est requis.",
            "integer"=> "L'ID de transaction doit être un entier."
        ],

        'order_id' => [
            'required' => "L'ID de commande est requis.",
        ],
    
    ],


    
];
