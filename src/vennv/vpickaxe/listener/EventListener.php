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

namespace vennv\vpickaxe\listener;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use vennv\vpickaxe\data\DataManager;

class EventListener implements Listener {

    public function __construct() {

    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        DataManager::getPickaxe($player);
    }

    public function onBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        DataManager::updateStats($player);
    }

}