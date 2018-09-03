<?php

return [
    'platform' => function () {
        return 'SuperV Platform @'.Current::port()->slug();
    },
];