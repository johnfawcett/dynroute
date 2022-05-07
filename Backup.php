<?php
namespace FreePBX\modules\Dynroute;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
  public function runBackup($id,$transaction){
    $this->addDependency('core');
    $this->addDependency('recordings');
    $configs = [
        'dynroutes' => $this->FreePBX->Dynroute->getAllDetails(),
        'entries' => $this->FreePBX->Dynroute->getAllEntries(),
    ];
    $this->addConfigs($configs);
  }
}
