<?php
return [
 'google'=>['client_id'=>env('GOOGLE_CLIENT_ID'),'client_secret'=>env('GOOGLE_CLIENT_SECRET'),'redirect'=>env('GOOGLE_REDIRECT_URI')],
 'kuartal_id'=>['issuer'=>rtrim(env('KUARTAL_ID_ISSUER','https://id.kuartal.id'),'/'),'client_id'=>env('KUARTAL_ID_CLIENT_ID'),'client_secret'=>env('KUARTAL_ID_CLIENT_SECRET'),'redirect'=>env('KUARTAL_ID_REDIRECT_URI')],
];