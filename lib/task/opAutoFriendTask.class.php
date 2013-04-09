<?php
/**
 *
 * @author     Mamoru Tejima <tejima@gmail.com>
 */
class opAutoFriendTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'cqc.jp';
    $this->name             = 'AutoFriend';
    $this->briefDescription = 'This plugin makes friend link automatically.';
    $this->databaseManager = null;
    $this->detailedDescription = <<<EOF
  [./symfony zuniv.us:AutoFriend]
EOF;

    $this->addOption('disconnectall', null,sfCommandOption::PARAMETER_NONE, 'disconnectall', null);
    $this->addOption('disconnect', null,sfCommandOption::PARAMETER_NONE, 'disconnect', null);
    $this->addOption('member_id', null, sfCommandOption::PARAMETER_OPTIONAL, 'member_id', null);
    $this->addOption('community_id', null, sfCommandOption::PARAMETER_OPTIONAL, 'community_id', null);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->databaseManager = new sfDatabaseManager($this->configuration);

    if($options['disconnectall']){
      echo "try disconnect all\n";
      $this->disconnectall();
      echo "disconnect all done.\n";
      return;
    }
    
    if($options['disconnect']){
      if($options['member_id'] && $options['community_id']){
        $this->autoFriendDisconnectCommunity($options['member_id'],$options['community_id']);
      }else{
        die('parameter requied.');
      }
      echo "disconnect\n";
      return;
    }

    if($options['member_id']){
      $this->autoFriendWithId($options['member_id']);
    }else if($options['community_id']){
      $this->autoFriendWithCommunityId($options['community_id']);
      echo "autoFriendWithCommunityId\n";
    }else{
      $this->autoFriendAll();
      echo "autoFriendAll\n";
    }
  }
  private function autoFriendAll(){
    Doctrine_Query::create()->delete('MemberRelationship s')->execute();
    
    $conn = $this->databaseManager->getDatabase(array_shift($this->databaseManager->getNames()))->getConnection();

    $stmt = $conn->prepare('insert into member_relationship (member_id_to,member_id_from,is_friend,is_friend_pre)'
    .' SELECT m1.id as member_id_to ,m2.id as member_id_from ,1 as is_friend , 0 as is_friend_pre FROM'
    .' member as m1,member as m2 WHERE m1.id != m2.id AND m1.is_login_rejected = 0 AND m2.is_login_rejected = 0');

    $stmt->execute();
  }
  private function autoFriendWithCommunityId($community_id = null){
    if(!$community_id){
      return;
    }
    $id_list = Doctrine_Query::create()
    ->select('cm.member_id')->from('CommunityMember cm')
    ->where("cm.community_id = ?",$community_id)->execute();

    //$id_list = Doctrine::getTable('CommunityMember')->getMemberIdsByCommunityId($community_id);
    if(count($id_list) >= 300){
      //当面300人以上は負荷の心配があるので制限する。
      return;
    }
    foreach($id_list as $id_from){
      foreach($id_list as $id_to){
        if($id_from['member_id'] != $id_to['member_id']){
          $relation = Doctrine::getTable('MemberRelationship')->retrieveByFromAndTo($id_from['member_id'],$id_to['member_id']);
          $relation2 = Doctrine::getTable('MemberRelationship')->retrieveByFromAndTo($id_to['member_id'],$id_from['member_id']);
          if(!$relation && !$relation2){
             $obj = new MemberRelationship();
             $obj->member_id_from = $id_from['member_id'];
             $obj->member_id_to = $id_to['member_id'];
             $obj->is_friend = 1;
             $obj->save();

             $obj2 = new MemberRelationship();
             $obj2->member_id_from = $id_to['member_id'];
             $obj2->member_id_to = $id_from['member_id'];
             $obj2->is_friend = 1;
             $obj2->save();
          }
        }
      }
    }
  }
  private function autoFriendWithId($member_id = null){
    if(!$member_id){
      return;
    }
    $conn = $this->databaseManager->getDatabase(array_shift($this->databaseManager->getNames()))->getConnection();
    //ターゲットメンバーの既存フレンドリンクを削除
    Doctrine_Query::create()->delete()->from('MemberRelationship')
      ->where('member_id_to = ?',$member_id)->orWhere('member_id_from = ?',$member_id)->execute();

    $stmt = $conn->prepare('insert into member_relationship (member_id_to,member_id_from,is_friend,is_friend_pre) select id as member_id_to , ? as member_id_from, 1 as is_friend, 0 as is_friend_pre from member where id != ? AND is_login_rejected = 0 UNION select ? as member_id_to , id as member_id_from , 1 as is_friend, 0 as is_friend_pre from member where id !=? AND is_login_rejected = 0 ;');
    $stmt->execute(array($member_id,$member_id,$member_id,$member_id));
  }
  private function autoFriendDisconnectCommunity($without_member_id,$community_id){
    $id_list = Doctrine_Query::create()
    ->select('cm.member_id')->from('CommunityMember cm')
    ->where("cm.community_id = ?",$community_id)->execute();

    foreach($id_list as $id){
      if($id['member_id'] == $without_member_id){
        //skip
      }else{
        $this->disconnectWitoutId($id['member_id'],$without_member_id);
      }
    }
  }
  private function disconnectWitoutId($target_member_id,$without_member_id){
    //ターゲットメンバーの既存フレンドリンクを削除
    Doctrine_Query::create()->delete()->from('MemberRelationship')
      ->where('member_id_to = ?',$target_member_id)->orWhere('member_id_from = ?',$target_member_id)->execute();

    if($target_member_id == $without_member_id){
      return;
    }
    //残すメンバーだけフレンドリンクしなおす
    $obj = new MemberRelationship();
    $obj->member_id_from = $target_member_id;
    $obj->member_id_to = $without_member_id;
    $obj->is_friend = 1;
    $obj->save();

    $obj2 = new MemberRelationship();
    $obj2->member_id_from = $without_member_id;
    $obj2->member_id_to = $target_member_id;
    $obj2->is_friend = 1;
    $obj2->save();
  }
  private function disconnectall(){
    Doctrine_Query::create()->delete('MemberRelationship')->execute();
  }
}
