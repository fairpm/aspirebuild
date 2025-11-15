<?php

#
#
# Parsedown Extra
# https://github.com/erusev/parsedown-extra
#
# (c) Emanuil Rusev
# http://erusev.com
#
# For the full license information, view the LICENSE file that was distributed
# with this source code.
#
#

namespace AspireBuild\Tools\Sideways;

use DOMDocument;
use DOMElement;

class SidewaysExtra extends Sideways
{
    function __construct(
        bool $breaksEnabled = false,
        bool $markupEscaped = false,
        bool $urlsLinked = true,
        bool $safeMode = false,
        bool $strictMode = false,
    ) {
        parent::__construct(
            breaksEnabled: $breaksEnabled,
            markupEscaped: $markupEscaped,
            urlsLinked   : $urlsLinked,
            safeMode     : $safeMode,
            strictMode   : $strictMode,
            extra: true,
        );
    }
}
