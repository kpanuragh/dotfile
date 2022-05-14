-- Just an example, supposed to be placed in /lua/custom/

local M = {}

-- make sure you maintain the structure of `core/default_config.lua` here,
-- example of changing theme:
M.plugins = {
  override = {
     ["nvim-treesitter/nvim-treesitter"] = {
       ensure_installed = {
         "html",
         "css",
         "php",
         "javascript"
      },
    }
  }
}

return M
