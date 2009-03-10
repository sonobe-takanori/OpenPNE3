<?php slot('submenu') ?>
<?php include_partial('submenu') ?>
<?php end_slot() ?>

<?php slot('title', __('メンバーリスト')); ?>

<form action="<?php echo url_for('member/list') ?>" method="get">
<table>
<?php echo $form ?>
<tr>
<td colspan="2"><input type="submit" value="検索" /></td>
</tr>
</table>
</form>

<?php if (!$pager->getNbResults()): ?>
<p><?php echo __('該当するメンバーは存在しません。') ?></p>
<?php else: ?>

<p>
<?php echo image_tag('icn_delete_account.gif', array('alt' => __('強制退会'))) ?>: <?php echo __('強制退会') ?>
&nbsp;
<?php echo image_tag('icn_passwd.gif', array('alt' => __('パスワード再発行'))) ?>: <?php echo __('パスワード再発行') ?>
&nbsp;
<?php echo image_tag('icn_blacklist.gif', array('alt' => __('携帯電話個体識別番号をブラックリストに登録'))) ?>: <?php echo __('携帯電話個体識別番号をブラックリストに登録') ?>
</p>

<table>

<tr>
<td colspan="<?php echo 6 + count($profiles) + 4 ?>">
<?php echo pager_navigation($pager, 'member/list?page=%d', true, '?'.$sf_request->getCurrentQueryString()) ?>
</td>
</tr>

<tr>
<th colspan="3"><?php echo __('操作') ?></th>
<th><?php echo __('ID') ?></th>
<th><?php echo __('ニックネーム') ?></th>
<th><?php echo __('招待者') ?></th>
<th><?php echo __('最終ログイン') ?></th>
<?php foreach ($profiles as $profile) : ?>
<th><?php echo $profile->getCaption() ?></th>
<?php endforeach; ?>
<th><?php echo __('PCメールアドレス') ?></th>
<th><?php echo __('携帯メールアドレス') ?></th>
<th><?php echo __('携帯電話個体識別番号（暗号化済）') ?></th>
</tr>

<?php foreach ($pager->getResults() as $i => $member): ?>
<tr style="background-color:<?php echo cycle_vars('member_list', '#fff, #eee') ?>;">
<td>
<?php if ($member->getId() != 1) : ?>
<?php echo link_to(image_tag('icn_delete_account.gif', array('alt' => __('強制退会'))), 'member/delete?id='.$member->getId()) ?>
<?php endif; ?>
</td>
<td>
<?php echo link_to(image_tag('icn_passwd.gif', array('alt' => __('パスワード再発行'))), 'member/reissuePassword?id='.$member->getId()) ?>
</td>
<td>
<?php echo link_to(image_tag('icn_blacklist.gif', array('alt' => __('携帯電話個体識別番号をブラックリストに登録'))), 'member/blacklist?uid='.$member->getConfig('mobile_uid')) ?>
</td>
<td><?php echo $member->getId() ?></td>
<td><?php echo $member->getName() ?></td>
<td><?php if ($member->getInviteMember()) : ?><?php echo $member->getInviteMember()->getName() ?><?php endif; ?></td>
<td><?php if ($member->getLastLoginTime()) : ?><?php echo date('y-m-d<b\r />H:i:s', $member->getLastLoginTime()) ?><?php endif; ?></td>
<?php foreach ($profiles as $profile) : ?>
<td><?php echo $member->getProfile($profile->getName()); ?></td>
<?php endforeach; ?>
<td><?php echo $member->getConfig('pc_address') ?></td>
<td><?php echo $member->getConfig('mobile_address') ?></td>
<td><?php echo $member->getConfig('mobile_uid') ?></td>
</tr>
<?php endforeach; ?>

</table>
<?php endif; ?>
