import Alpine from 'alpinejs'

import collapse from '@alpinejs/collapse'


import Tooltip from '@ryangjchandler/alpine-tooltip'
import intersect from '@alpinejs/intersect' // <- Push this

// ...
Alpine.plugin(collapse)
Alpine.plugin(Tooltip)
Alpine.plugin(intersect) // <- Push this

// ...

Alpine.start()