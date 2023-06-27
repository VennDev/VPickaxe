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

namespace vennv\vpickaxe\async;

final class Async {

    private static array $awaiting = [];
    private static array $listTerminated = [];

    private function addWait(Await $await) : bool {
        try {
            self::$awaiting[] = $await;
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function dropAwaits() : bool {
        try {
            foreach (self::$listTerminated as $index) {
                unset(self::$awaiting[$index]);
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function addTerminated(int $index) : bool {
        try {
            self::$listTerminated[] = $index;
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function processFiber(?\Fiber $fiber, int $index) : bool {
        if (!is_null($fiber)) {
            if ($fiber->isSuspended() && !$fiber->isTerminated()) {
                $fiber->resume();
            } elseif ($fiber->isTerminated()) {
                $this->addTerminated($index);
            }
            return true;
        }
        return false;
    }

    public function await(\Fiber $fiber) : mixed {

        $await = new Await(
            \Fiber::getCurrent(),
            $fiber
        );

        $this->addWait($await);

        $fiber->start();

        while (!$fiber->isTerminated()) {

            $fiber->resume();

            if (!$fiber->isTerminated()) {
                \Fiber::suspend();
            } else {
                break;
            }
        }

        return $fiber->getReturn();
    }

    public function run() : void {

        while (count(self::$awaiting) > 0) {

            foreach (self::$awaiting as $index => $data) {

                $parent = $data->getCurrent();
                $fiber = $data->getFiber();

                if (!$this->processFiber($parent, $index)) {
                    $this->processFiber($fiber, $index);
                }

            }

            $this->dropAwaits();

            self::$awaiting = array_values(self::$awaiting);
        }

    }

}