<?php
namespace FreePBX\modules\Dynroute;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
	public function runRestore(){
		$configs = $this->getConfigs();
		foreach ($configs['dynroutes'] as $id => $dynroute) {
			$this->FreePBX->Dynroute->saveDetail($dynroute['0']);
		}

		foreach($configs['entries'] as $id => $entry) {
			$this->FreePBX->Dynroute->saveEntry($id, $entry);
		}
	}
}

