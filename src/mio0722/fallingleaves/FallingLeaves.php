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

namespace mio0722\fallingleaves;

use mio0722\fallingleaves\block\Leaves;
use pocketmine\block\BlockBreakInfo as BreakInfo;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIdHelper;
use pocketmine\block\BlockToolType as ToolType;
use pocketmine\block\BlockTypeInfo as Info;
use pocketmine\block\utils\TreeType;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\resourcepacks\ZippedResourcePack;
use Symfony\Component\Filesystem\Path;
use ReflectionProperty;

final class FallingLeaves extends PluginBase {

	protected function onLoad(): void{
		$this->saveResource("LeafRP.mcpack", true);
		$pack = new ZippedResourcePack(Path::join($this->getDataFolder(), "LeafRP.mcpack"));
		$rpManager = $this->getServer()->getResourcePackManager();
		$resourcePacks = new \ReflectionProperty($rpManager, "resourcePacks");
		$resourcePacks->setAccessible(true);
		$resourcePacks->setValue($rpManager, array_merge($resourcePacks->getValue($rpManager), [$pack]));
		$uuidList = new \ReflectionProperty($rpManager, "uuidList");
		$uuidList->setAccessible(true);
		$uuidList->setValue($rpManager, $uuidList->getValue($rpManager) + [strtolower($pack->getPackId()) => $pack]);
		$serverForceResources = new \ReflectionProperty($rpManager, "serverForceResources");
		$serverForceResources->setAccessible(true);
		$serverForceResources->setValue($rpManager, true);
	}

	public function onEnable(): void{
		$leavesBreakInfo = new Info(new class(0.2, ToolType::HOE) extends BreakInfo {
		public function getBreakTime(Item $item): float{
			if($item->getBlockToolType() === ToolType::SHEARS) {
				return 0.0;
			}
			return parent::getBreakTime($item);
		}
		});
		
		foreach(TreeType::getAll() as $treeType) {
			$name = $treeType->getDisplayName();
 			BlockFactory::getInstance()->register(new Leaves(BlockLegacyIdHelper::getLeavesIdentifier($treeType), $name . " Leaves", $leavesBreakInfo, $treeType), true);
		}
	}
}