-- Just an example, supposed to be placed in /lua/custom/

local M = {}

-- make sure you maintain the structure of `core/default_config.lua` here,
-- example of changing theme:
M.plugins = {
  user = {
    ["goolord/alpha-nvim"] = {
       disable = false
    },
 },
}

return M
