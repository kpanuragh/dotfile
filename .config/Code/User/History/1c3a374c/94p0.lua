-- Just an example, supposed to be placed in /lua/custom/

local M = {}
local userPlugins = require "custom.plugins"
-- make sure you maintain the structure of `core/default_config.lua` here,
-- example of changing theme:

M.ui = {
   theme = "gruvchad",
}

local options = {}

local ascii = {
   "~^^^^^^^^::~5#######&&&&&&##&&&&&######G!::::::^^?",
   "~^^^^^^^^::P#######&&&&&&&##&&&&&########J^::::^^7",
   "~^^^^^^^^:Y########&&&&&&&&&&&&&&&&&#####&5:::::^!",
   "~~^^^^^^:?##########&&&&&&&&&&&&&&&&######G^::::^~",
   "~^^^^^^^:7B&&&&#####&&&&&&&&&&&&&&&&#######Y::::^~",
   "~^^^^^^::7#&&&PYB#####&&&&&&&&&&&&&&&&&&##B~::::^^",
   "~^^^^^^::.7&&#!^!YGB#&&&&&&&#Y?5PGBGG&&&&&J.::::^^",
   "~^^^^^::::~#&G~~~^~!777?JJ?!~^^^^~~~~B&&&G^:::::^^",
   "~^^^^^::::^B&P~~~~~^^^^^^^^^^~~~~~~~~G&&#!::::::^^",
   "~^^^^::::::GB7~~~~~~~^^^^^~~~~~~~~~~~P&&J.::::::^^",
   "~^^^::::::~5G~~~^^^^^^^^^^^^^^^^^^^^~~PB^:::::::^^",
   "^^^:::::::~7B?^~?Y55YYJ!^^~^!??JJJJJ!~B5~:::::::^^",
   "^^^::::::::7GJ!!?P?GG?Y?^^^^?JG#5??J?!P7^:::::::^^",
   "^^^^:::::::~5G7!!!~!!^!7????J^~7~~777Y7^::::::::^^",
   "^^^^::::::::~5~^~~~~~~^?Y!JJ~~~~~~~~~P!::::::::^^^",
   "^^^^:::::::::PY!!~~~~~7?~^~?!~~~~~~~7J:::::::::^^^",
   "^^^^::::::::.JG!!!7?!7!^^^~~!77777!!J:.:::::::::^^",
   "^^^^^^^^~~!7??BG!?##BBPJ!~7YPPPPJ!~?J77777777!~^^^",
   "YYY55PPPGGGGGGG##P#5?PB#BB##GP5P?7JGGGGGGGGGGGGP5J",
   "BGGGGGGGGGGGGGGGG&&B7~!JJJ?7~^JBGGGGGGGGGGGGGGGGGB",
   "BGGGGGGGGGGGGGGGY5&&#5JGBG7~!!G&BGGGGGGGGGGGGGGGGG",
   "BGGGGGGGGGGGGGGGGYY#&&&&&&####BGGGGGGGGGGGGGGGGGGG",
   "BGGGGGGGGGGGGGGGGG5JG&&&&&&&#PPGGGGGGGGGGGGGGGGGGG",
   }
options.header = {
   type = "text",
   val = ascii,
   opts = {
      position = "center",
      hl = "AlphaHeader",
   },
}
M.plugins = {
   user = userPlugins,
   override = {
      ["goolord/alpha-nvim"] = options
   }
}
return M
