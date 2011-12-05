<?php

/**
 * PluginaReusableSlotTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PluginaReusableSlotTable extends Doctrine_Table
{
  /**
   * Returns an instance of this class.
   *
   * @return object PluginaReusableSlotTable
   */
  public static function getInstance()
  {
    return Doctrine_Core::getTable('PluginaReusableSlot');
  }
  
  public function findOneBySlot($page_id, $area_name, $permid)
  {
    return $this->createQuery('r')->where('r.page_id = ? AND r.area_name = ? AND r.permid = ?', array($page_id, $area_name, $permid))->fetchOne();
  }
  
  /**
   * Accepts an aReusableSlot or an array hydrated with the same columns. Returns the
   * actual Apostrophe slot being reused, or null if its page no longer exists or
   * it does not exist in the current version of its area on the page
   */
  static public function getReusedSlot($reusableSlot)
  {
    $q = aPageTable::queryWithSlot($reusableSlot['area_name']);
    $q->andWhere('p.id = ?', $reusableSlot['page_id']);
    $q->andWhere('avs.permid = ?', $reusableSlot['permid']);
    $page = $q->fetchOne();
    if (!$page)
    {
      return null;
    }
    $slots = $page->getSlotsByAreaName($reusableSlot['area_name']);
    if (!isset($slots[$reusableSlot['permid']]))
    {
      return null;
    }
    $slot = $slots[$reusableSlot['permid']];
    $values = $slot->getArrayValue();
    if (isset($values['reuse']['id']))
    {
      // Reference to yet another slot. We don't allow this
      // (could lead to infinite recursion, it's certainly inefficient)
      return null;
    }
    return $slot;
  }
  
  /**
   * Remove any reusable slot with the given label that does not actually point to
   * a valid reused slot anymore. We do this when we really have to: just before checking 
   * a newly saved reusable slot's label for uniqueness
   */
  static public function purgeOrphanByLabel($label)
  {
    $slot = Doctrine::getTable('aReusableSlot')->findOneByLabel($label);
    if (!$slot->getReusedSlot())
    {
      $slot->delete();
    }
  }  
}