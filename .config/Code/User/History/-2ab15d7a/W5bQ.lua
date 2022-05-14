local present, alpha = pcall(require, "alpha")

if not present then
   return
end

local function button(sc, txt, keybind)
   local sc_ = sc:gsub("%s", ""):gsub("SPC", "<leader>")

   local opts = {
      position = "center",
      text = txt,
      shortcut = sc,
      cursor = 5,
      width = 36,
      align_shortcut = "right",
      hl = "AlphaButtons",
   }

   if keybind then
      opts.keymap = { "n", sc_, keybind, { noremap = true, silent = true } }
   end

   return {
      type = "button",
      val = txt,
      on_press = function()
         local key = vim.api.nvim_replace_termcodes(sc_, true, false, true)
         vim.api.nvim_feedkeys(key, "normal", false)
      end,
      opts = opts,
   }
end

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

options.buttons = {
   type = "group",
   val = {
      button("SPC f f", "  Find File  ", ":Telescope find_files<CR>"),
      button("SPC f o", "  Recent File  ", ":Telescope oldfiles<CR>"),
      button("SPC f w", "  Find Word  ", ":Telescope live_grep<CR>"),
      button("SPC b m", "  Bookmarks  ", ":Telescope marks<CR>"),
      button("SPC t h", "  Themes  ", ":Telescope themes<CR>"),
      button("SPC e s", "  Settings", ":e $MYVIMRC | :cd %:p:h <CR>"),
   },
   opts = {
      spacing = 1,
   },
}

options = nvchad.load_override(options, "goolord/alpha-nvim")

-- dynamic header padding
local fn = vim.fn
local marginTopPercent = 0.3
local headerPadding = fn.max { 2, fn.floor(fn.winheight(0) * marginTopPercent) }

alpha.setup {
   layout = {
      { type = "padding", val = headerPadding },
      options.header,
      { type = "padding", val = 2 },
      options.buttons,
   },
   opts = {},
}
