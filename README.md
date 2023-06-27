# VPickaxe
<img src="https://github.com/VennDev/VPickaxe/blob/main/icon.png" alt="VPickaxe" height="150" width="150" />
- This is Plugin VPickaxe for PocketMine-PMMP 5.x
- This Pickaxe makes your server experience even better with the levels that this plugin brings.

# Features
- Integrate and use everything based on commands to execute according to the level that you have.
- It's all done on asynchronous which makes the plugin lightweight.

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
max-level: 2

# Settings for level stage.
level-stage:
  2:
    # The message to send to the player when they reach level 2.
    message:
      - '§aYou will now receive a reward for reaching level 2!'

    # The enchantment to give the player when they reach level 2.
    # You can see list enchants here: https://github.com/VennDev/NameEnchantPMMP
    # In the source code of PMMP comes "enchantment." you should remove it and get their name.
    enchants:
      digging:
        level: 1
        command: 'enchant %player% efficiency 1'

    # When the player breaks a block, they will receive a reward.
    rewards-on-break-block:
      chance: 10 # The chance to receive a reward. If you want to receive a reward every time, set it to 100.
      random-mode: true
      random-count: 1
      commands:
        - 'give %player% diamond 1'
        - 'give %player% iron_ingot 1'
        - 'give %player% gold_ingot 1'

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

# Credits
- Email: pnam5005@gmail.com
- Paypal: lifeboat909@gmail.com
