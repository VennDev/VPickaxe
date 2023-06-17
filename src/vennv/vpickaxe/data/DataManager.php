<?php

/**
 * VPickaxe - PocketMine plugin.
 * Copyright (C) 2023 - 2025 VennDev
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace vennv\vpickaxe\data;

use pocketmine\lang\KnownTranslationFactory;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\item\Item;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use vennv\vpickaxe\events\PickaxeUpLevelEvent;
use vennv\vpickaxe\utils\ItemUtil;
use vennv\vpickaxe\VPickaxe;

class DataManager {

    public static function getConfig(): Config {
        return VPickaxe::getInstance()->getConfig();
    }

    public static function getOwner(Item $item): string {
        return $item->getNamedTag()->getString("owner");
    }

    public static function setOwner(Item $item, string $owner): Item {
        $item->getNamedTag()->setString("owner", $owner);
        $item->setNamedTag($item->getNamedTag());
        return $item;
    }

    public static function getXuid(Item $item): string {
        return $item->getNamedTag()->getString("xuid");
    }

    public static function setXuid(Item $item, string $xuid): Item {
        $item->getNamedTag()->setString("xuid", $xuid);
        $item->setNamedTag($item->getNamedTag());
        return $item;
    }

    public static function getLevel(Item $item): int {
        return $item->getNamedTag()->getInt("level");
    }

    public static function setLevel(Item $item, int $level): Item {
        $item->getNamedTag()->setInt("level", $level);
        $item->setNamedTag($item->getNamedTag());
        return $item;
    }

    public static function getExp(Item $item): float {
        return $item->getNamedTag()->getFloat("exp");
    }

    public static function setExp(Item $item, float $exp): Item {
        $item->getNamedTag()->setFloat("exp", $exp);
        $item->setNamedTag($item->getNamedTag());
        return $item;
    }

    public static function addExp(Item $item, float $exp): Item {
        $exp = round($exp, 3);
        $item->getNamedTag()->setFloat("exp", self::getExp($item) + $exp);
        $item->setNamedTag($item->getNamedTag());
        return $item;
    }

    public static function getNextExp(Item $item): float {
        $level = self::getLevel($item);
        return $level * self::getConfig()->get("formula");
    }

    public static function getNextLevel(Item $item): int {
        return self::getLevel($item) + 1;
    }

    public static function hasPickaxe(Player $player): bool {
        $config = new Config("vpickaxe.yml", Config::YAML);
        return $config->exists($player->getName());
    }

    public static function isPickaxe(Item $item): bool {
        try {
            return $item->getNamedTag()->getString("vpickaxe") === "vpickaxe";
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @throws \JsonException
     */
    public static function getPickaxe(Player $player): void {
        $hasPickaxe = self::hasPickaxe($player);
        if (!$hasPickaxe) {

            $item = ItemUtil::getItem("diamond_pickaxe");
            $item->setCustomName(self::getConfig()->get("default-name"));
            $item->getNamedTag()->setString("vpickaxe", "vpickaxe");

            $item = self::setOwner($item, $player->getName());
            $item = self::setLevel($item, 1);
            $item = self::setExp($item, 0.0);
            $item = self::setXuid($item, $player->getXuid());

            $lore = self::getConfig()->get("lore");
            foreach ($lore as $key => $value) {
                $lore[$key] = self::checkLore($item, $value);
            }
            $item->setLore($lore);

            $player->getInventory()->addItem($item);

            $config = new Config("vpickaxe.yml", Config::YAML);
            $config->set($player->getName(), true);
            $config->save();
        }
    }

    public static function checkLore(Item $item, string $text): string {
        $text = explode(" ", $text)[0];
        $data = self::getConfig()->get("lore");
        foreach ($data as $value) {
            if (count(explode($text, $value)) > 1) {
                $stats = ["%owner%", "%level%", "%exp%", "%next_exp%", "%next_level%"];
                $replace = [self::getOwner($item), self::getLevel($item), round(self::getExp($item), 2), self::getNextExp($item), self::getNextLevel($item)];
                return str_replace($stats, $replace, $value);
            }
        }
        return $text;
    }

    /**
     * @throws \Throwable
     */
    public static function updateLore(): void {
        foreach (VPickaxe::getInstance()->getServer()->getOnlinePlayers() as $player) {
            $fiber = new \Fiber(function() use ($player) {
                $item = $player->getInventory()->getItemInHand();
                $hasPickaxe = self::hasPickaxe($player);
                if ($hasPickaxe) {
                    $isPickaxe = self::isPickaxe($item);
                    if ($isPickaxe) {
                        $lore = $item->getLore();
                        foreach ($lore as $k => $v) {
                            $lore[$k] = self::checkLore($item, $v);
                        }
                        $item->setLore($lore);
                        $player->getInventory()->setItemInHand($item);
                    }
                }
            });
            $fiber->start();
        }
    }

    /**
     * @throws \Throwable
     */
    public static function updateStats(Player $player): void {
        $fiber = new \Fiber(function() use ($player) {
            $item = $player->getInventory()->getItemInHand();
            $hasPickaxe = self::hasPickaxe($player);
            if ($hasPickaxe) {
                $isPickaxe = self::isPickaxe($item);
                if ($isPickaxe) {
                    $exp = self::getExp($item);
                    $level = self::getLevel($item);
                    $nextExp = self::getNextExp($item);
                    $nextLevel = self::getNextLevel($item);
                    if ($level >= self::getConfig()->get("max-level")) return;
                    if ($exp >= $nextExp) {
                        self::setLevel($item, $nextLevel);
                        self::setExp($item, 0.0);
                        $config = self::getConfig()->get("level-stage");
                        if (isset($config[$nextLevel])) {
                            $data = $config[$nextLevel];
                            if (isset($data["message"])) {
                                foreach ($data["message"] as $message) {
                                    $player->sendMessage($message);
                                }
                            }
                            if (isset($data["first-run-command"])) {
                                $mData = $data["first-run-command"];
                                self::runCommand($player, $mData);
                            }
                        }
                        $event = new PickaxeUpLevelEvent($player, $nextLevel, $item);
                        $event->call();
                    }
                    $item = self::addExp($item, rand(1, $level + 1) / 10);
                    $player->getInventory()->setItemInHand($item);
                }
            }
        });
        $fiber->start();
    }

    public static function runCommand(Player $player, array $mData) : void {
        if ($mData["random-mode"] === true) {
            $alwaysCommands = array_rand($mData["commands"], $mData["random-count"]);
            if (!is_array($alwaysCommands)) $alwaysCommands = [$alwaysCommands];
            foreach ($alwaysCommands as $key) {
                $command = $mData["commands"][$key];
                $namePlayer = $player->getName();
                $command = str_replace("{player}", $namePlayer, $command);
                $command = str_replace("%player%", $namePlayer, $command);
                VPickaxe::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(VPickaxe::getInstance()->getServer(), VPickaxe::getInstance()->getServer()->getLanguage()), $command);
            }
        } else {
            foreach ($mData["commands"] as $command) {
                $namePlayer = $player->getName();
                $command = str_replace("{player}", $namePlayer, $command);
                $command = str_replace("%player%", $namePlayer, $command);
                VPickaxe::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(VPickaxe::getInstance()->getServer(), VPickaxe::getInstance()->getServer()->getLanguage()), $command);
            }
        }
    }

    /**
     * @throws \Throwable
     */
    public static function getCommands(): void {
        foreach (VPickaxe::getInstance()->getServer()->getOnlinePlayers() as $player) {
            $fiber = new \Fiber(function() use ($player) {
                $item = $player->getInventory()->getItemInHand();
                $hasPickaxe = self::hasPickaxe($player);
                if ($hasPickaxe) {
                    $isPickaxe = self::isPickaxe($item);
                    if ($isPickaxe) {
                        $config = self::getConfig()->get("level-stage");
                        if (isset($config[self::getLevel($item)])) {
                            $data = $config[self::getLevel($item)];
                            if (isset($data["always-run-command"])) {
                                $mData = $data["always-run-command"];
                                self::runCommand($player, $mData);
                            }
                            $haveEnchant = [];
                            $enchants = $data["enchants"];
                            $enchantsItem = $item->getEnchantments();
                            foreach ($enchantsItem as $enchant) {
                                $name = $enchant->getType()->getName();
                                if ($name instanceof Translatable) {
                                    $name = $name->getText();
                                }
                                $haveEnchant[explode("enchantment.", $name)[1]] = $enchant->getLevel();
                            }
                            foreach ($enchants as $name => $enchant) {
                                if (isset($haveEnchant[$name])) {
                                    if ($haveEnchant[$name] >= $enchant["level"]) {
                                        continue;
                                    }
                                }
                                $namePlayer = $player->getName();
                                $command = str_replace("{player}", $namePlayer, $enchant["command"]);
                                $command = str_replace("%player%", $namePlayer, $command);
                                VPickaxe::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(VPickaxe::getInstance()->getServer(), VPickaxe::getInstance()->getServer()->getLanguage()), $command);
                            }
                        }
                    }
                }
            });
            $fiber->start();
        }
    }

    public static function onBreak(Player $player) : void {
        $fiber = new \Fiber(function() use ($player) {
            $item = $player->getInventory()->getItemInHand();
            $hasPickaxe = self::hasPickaxe($player);
            if ($hasPickaxe) {
                $isPickaxe = self::isPickaxe($item);
                if ($isPickaxe) {
                    $level = self::getLevel($item);
                    $config = self::getConfig()->get("level-stage");
                    if (isset($config[$level])) {
                        $data = $config[$level];
                        if (isset($data["rewards-on-break-block"])) {
                            $mData = $data["rewards-on-break-block"];
                            if (rand(0, 100) <= $mData["chance"]) {
                                self::runCommand($player, $mData);
                            }
                        }
                    }
                }
            }
        });
        $fiber->start();
    }

}