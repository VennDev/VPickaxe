# VPickaxe
- This is Plugin VPickaxe for PocketMine-PMMP 5.x

# Config
```config
---

# Default name for Pickaxe
default-name: "§r§l§bV§aPickaxe§r"

# Lore for Pickaxe
lore:
  - "§r§7Owner: §e%owner%§r"
  - "§r§7Level: §e%level%§r"
  - "§r§7Exp: §e%exp%§r/§e%next_exp%§r"
  - "§r§7Next Level: §e%next_level%§r"

# The number is used to calculate the player's next level.
formula: 5

# Max level of the player.
max-level: 100

# Settings for level stage.
level-stage:
  2:
    # The message to send to the player when they reach level 2.
    message:
      - '§aYou will now receive a reward for reaching level 2!'

    # The enchantment to give the player when they reach level 2.
    # You can see list enchants here: https://github.com/VennDev/NameEnchantPMMP
    enchants:
      digging: 'enchant %player% efficiency 1'

    # The command always to run when the player reaches level 2.
    always-run-command:
      random-mode: true
      random-count: 1
      commands:
        - 'say %player% message a!'
        - 'say %player% message b!'

    # The command to run when the player reaches level 2 for the first time.
    first-run-command:
      random-mode: false
      random-count: 1
      commands:
        - 'say %player% has reached level 5 for the first time!'
        - 'give %player% diamond 1'
        - 'enchant %player% efficiency 1'

...
```

# Images
<img src="https://github.com/VennDev/images/blob/main/VPickaxe.png" alt="VPickaxe" height="300" width="400" />
