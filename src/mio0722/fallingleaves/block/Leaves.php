<?php

/**
 * FallingLeaves: Makes leaves fall from trees of the world
 * Copyright (C) 2023 Mio0722
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

declare(strict_types=1);

namespace mio0722\fallingleaves\block;

use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Leaves as VanillaLeaves;
use pocketmine\block\utils\TreeType;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;

class Leaves extends VanillaLeaves {

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, TreeType $treeType) {
		parent::__construct($idInfo, $name, $typeInfo, $treeType);
	}

	public function onRandomTick(): void{
		if($this->noDecay) {
			$packet = new SpawnParticleEffectPacket();
			$packet->position = $this->position;
			$packet->particleName = "minecraft:leaf";
			$world = $this->position->getWorld();
			$world->broadcastPacketToViewers($this->position, $packet);
			return;
		}

		if(!$this->noDecay && $this->checkDecay) {
			$ev = new LeavesDecayEvent($this);
			$ev->call();
			$world = $this->position->getWorld();
			if($ev->isCancelled() || $this->findLog($this->position)) {
				$this->checkDecay = false;
				$world->setBlock($this->position, $this, false);
			}
			else {
				$world->useBreakOn($this->position);
			}
		}
	}
}
