<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

class opFriendTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'opMinnatomodachi';
    $this->name             = 'batch';
    $this->briefDescription = 'Creates the plugin definition file and archive the OpenPNE plugin.';
    $this->detailedDescription = <<<EOF
The [opPlugin:release|INFO] task creates the plugin definition file, and archive the OpenPNE plugin.
Call it with:

  [./symfony opPlugin:release opSamplePlugin|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    Doctrine_Query::create()->delete('MemberRelationship s')->execute();
    $conn = $databaseManager->getDatabase(array_shift($databaseManager->getNames()))->getConnection();
    $stmt = $conn->prepare('insert into member_relationship (member_id_to,member_id_from,is_friend,is_friend_pre) SELECT m1.id as member_id_to ,m2.id as member_id_from ,1 as is_friend , 0 as is_friend_pre FROM member as m1,member as m2 WHERE m1.id != m2.id');
    $stmt->execute();
  }
}
