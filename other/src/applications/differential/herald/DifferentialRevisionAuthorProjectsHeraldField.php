<?php

final class DifferentialRevisionAuthorProjectsHeraldField
  extends DifferentialRevisionHeraldField {

  const FIELDCONST = 'differential.revision.author.projects';

  public function getHeraldFieldName() {
    return pht("Author's projects");
  }

  public function getHeraldFieldValue($object) {
    return PhabricatorEdgeQuery::loadDestinationPHIDs(
      $object->getAuthorPHID(),
      PhabricatorProjectMemberOfProjectEdgeType::EDGECONST);
  }

  protected function getHeraldFieldStandardType() {
    return self::STANDARD_PHID_LIST;
  }

  protected function getDatasource() {
    return new PhabricatorProjectDatasource();
  }

}
