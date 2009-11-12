<?php

include_once dirname(__FILE__) . '/../../../bootstrap/unit.php';
include_once dirname(__FILE__) . '/../../../bootstrap/database.php';
sfContext::createInstance($configuration);
$user = sfContext::getInstance()->getUser();
$user->setAuthenticated(true);
$user->setMemberId(1);

$t = new lime_test(41, new lime_output_color());

$table = Doctrine::getTable('Community');
$member1 = Doctrine::getTable('Member')->findOneByName('A');
$member2 = Doctrine::getTable('Member')->findOneByName('B');
$community1 = Doctrine::getTable('Community')->findOneByName('CommunityA');
$community4 = Doctrine::getTable('Community')->findOneByName('CommunityD');

//------------------------------------------------------------
$t->diag('CommunityTable');
$t->diag('CommunityTable::retrievesByMemberId()');
$communities = $table->retrievesByMemberId(1);
$t->is(count($communities), 4, 'retrievesByMemberId() returns 4 communities');
$communities = $table->retrievesByMemberId(1, 1);
$t->is(count($communities), 1, 'retrievesByMemberId() returns 1 communities');
$communities = $table->retrievesByMemberId(1, 1, true);
$t->is(count($communities), 1, 'retrievesByMemberId() returns 1 communities');
$t->ok(!$table->retrievesByMemberId(999), 'retrievesByMemberId() return null');

//------------------------------------------------------------
$t->diag('CommunityTable::getJoinCommunityListPager()');
$pager = $table->getJoinCommunityListPager(1);
$t->isa_ok($pager, 'sfDoctrinePager', 'getJoinCommunityListPager() returns a sfDoctrinePager');
$t->is($pager->getNbResults(), 4, 'getNbResults() returns 4');

$pager = $table->getJoinCommunityListPager(999);
$t->isa_ok($pager, 'sfDoctrinePager', 'getJoinCommunityListPager() returns a sfDoctrinePager');
$t->is($pager->getNbResults(), 0, 'getNbResults() returns 0');

//------------------------------------------------------------
$t->diag('CommunityTable::getCommunityMemberListPager()');
$pager = $table->getCommunityMemberListPager(1);
$t->isa_ok($pager, 'sfDoctrinePager', 'getCommunityMemberListPager() returns a sfDoctrinePager');
$t->is($pager->getNbResults(), 2, 'getNbResults() returns 2');

$pager = $table->getCommunityMemberListPager(999);
$t->isa_ok($pager, 'sfDoctrinePager', 'getCommunityMemberListPager() returns a sfDoctrinePager');
$t->is($pager->getNbResults(), 0, 'getNbResults() returns 0');

//------------------------------------------------------------
$t->diag('CommunityTable::getIdsByMemberId()');
$communityIds = $table->getIdsByMemberId(1);
$t->is(count($communityIds), 4, 'getIdsByMemberId() returns 4 ids');
$t->is($communityIds, array(1, 3, 4, 5), 'getIdsByMemberId() returns array(1, 3, 4, 5)');

//------------------------------------------------------------
$t->diag('CommunityTable::getDefaultCommunities()');
$communities = $table->getDefaultCommunities();
$t->is(count($communities), 2, 'getDefaultCommunities() returns 2 communities');

//------------------------------------------------------------
$t->diag('CommunityTable::getChangeAdminRequestCommunities()');
$communities = $table->getChangeAdminRequestCommunities();
$t->cmp_ok($communities, '===' ,null, 'getChangeAdminRequestCommunities() returns null');

$communities = $table->getChangeAdminRequestCommunities(2);
$t->is(count($communities), 1, 'getChangeAdminRequestCommunities() returns a community');

$communities = $table->getChangeAdminRequestCommunities(1);
$t->cmp_ok($communities, '===' ,null, 'getChangeAdminRequestCommunities() returns null');

$communities = $table->getChangeAdminRequestCommunities(999);
$t->cmp_ok($communities, '===' ,null, 'getChangeAdminRequestCommunities() returns null');

//------------------------------------------------------------
$t->diag('CommunityTable::countChangeAdminRequestCommunities()');
$t->is($table->countChangeAdminRequestCommunities(), 0, 'countChangeAdminRequestCommunities() returns 0');
$t->is($table->countChangeAdminRequestCommunities(1), 0, 'countChangeAdminRequestCommunities() returns 0');
$t->is($table->countChangeAdminRequestCommunities(2), 1, 'countChangeAdminRequestCommunities() returns 1');
$t->is($table->countChangeAdminRequestCommunities(999), 0, 'countChangeAdminRequestCommunities() returns 0');

//------------------------------------------------------------
$t->diag('ACL Test');
$t->ok($community1->isAllowed($member1, 'view'));
$t->ok($community1->isAllowed($member2, 'view'));
$t->ok($community1->isAllowed($member1, 'edit'));
$t->ok(!$community1->isAllowed($member2, 'edit'));

//------------------------------------------------------------
$t->diag('CommunityTable::adminConfirmList()');
$event = new sfEvent('subject', 'name', array('member' => $member1));
$t->ok(!CommunityTable::adminConfirmList($event));

$event = new sfEvent('subject', 'name', array('member' => $member2));
$t->ok(CommunityTable::adminConfirmList($event));
$t->is(count($event->getReturnValue()), 1);

//------------------------------------------------------------
$t->diag('CommunityTable::processAdminConfirm()');
$event = new sfEvent('subject', 'name', array('member' => $member2, 'id' => $community4->getId(), 'is_accepted' => true));
$t->ok($community4->isAdmin($member1->getId()));
$t->ok(!$community4->isAdmin($member2->getId()));
$t->ok(CommunityTable::processAdminConfirm($event));
$t->ok(!$community4->isAdmin($member1->getId()));
$t->ok($community4->isAdmin($member2->getId()));

$cm1 = Doctrine::getTable('CommunityMember')->retrieveByMemberIdAndCommunityId($member1->getId(), $community4->getId());
$cm1->setPosition('admin');
$cm1->save();
$cm2 = Doctrine::getTable('CommunityMember')->retrieveByMemberIdAndCommunityId($member2->getId(), $community4->getId());
$cm2->setPosition('admin_confirm');
$cm2->save();

$event = new sfEvent('subject', 'name', array('member' => $member2, 'id' => $community4->getId(), 'is_accepted' => false));
$t->ok($community4->isAdmin($member1->getId()));
$t->ok(!$community4->isAdmin($member2->getId()));
$t->ok(CommunityTable::processAdminConfirm($event));
$t->ok($community4->isAdmin($member1->getId()));
$t->ok(!$community4->isAdmin($member2->getId()));

$event = new sfEvent('subject', 'name', array('member' => $member2, 'id' => 999, 'is_accepted' => false));
$t->ok(!CommunityTable::processAdminConfirm($event));
