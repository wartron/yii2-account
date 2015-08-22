<?php

use \wartron\yii2account\models\Token;

$time = time();

return [
    'confirmation' => [
        'account_id'    =>  hex2bin('22f24b97aade11e2aced000c29ae5e1b'),
        'code'          =>  'NO2aCmBIjFQX624xmAc3VBu7Th3NJoa6',
        'type'          =>  Token::TYPE_CONFIRMATION,
        'created_at'    =>  $time,
    ],
    'expired_confirmation' => [
        'account_id'    =>  hex2bin('33f24b97aade11e2aced000c29ae5e1b'),
        'code'          =>  'qxYa315rqRgCOjYGk82GFHMEAV3T82AX',
        'type'          =>  Token::TYPE_CONFIRMATION,
        'created_at'    =>  $time - 86401,
    ],
    'expired_recovery' => [
        'account_id'    =>  hex2bin('55f24b97aade11e2aced000c29ae5e1b'),
        'code'          =>  'a5839d0e73b9c525942c2f59e88c1aaf',
        'type'          =>  Token::TYPE_RECOVERY,
        'created_at'    =>  $time - 21601,
    ],
    'recovery' => [
        'account_id'    =>  hex2bin('66f24b97aade11e2aced000c29ae5e1b'),
        'code'          =>  '6f5d0dad53ef73e6ba6f01a441c0e602',
        'type'          =>  Token::TYPE_RECOVERY,
        'created_at'    =>  $time,
    ],
];
