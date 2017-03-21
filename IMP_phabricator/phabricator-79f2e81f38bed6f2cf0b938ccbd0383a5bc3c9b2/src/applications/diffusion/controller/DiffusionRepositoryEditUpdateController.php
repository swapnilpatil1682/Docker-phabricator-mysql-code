<?php

final class DiffusionRepositoryEditUpdateController
  extends DiffusionRepositoryEditController {

  protected function processDiffusionRequest(AphrontRequest $request) {
    $viewer = $request->getUser();
    $drequest = $this->diffusionRequest;
    $repository = $drequest->getRepository();

    $repository = id(new PhabricatorRepositoryQuery())
      ->setViewer($viewer)
      ->requireCapabilities(
        array(
          PhabricatorPolicyCapability::CAN_VIEW,
          PhabricatorPolicyCapability::CAN_EDIT,
        ))
      ->withIDs(array($repository->getID()))
      ->executeOne();
    if (!$repository) {
      return new Aphront404Response();
    }

    $edit_uri = $this->getRepositoryControllerURI($repository, 'edit/');

    if ($request->isFormPost()) {
      $params = array(
        'callsigns' => array(
          $repository->getCallsign(),
        ),
      );

      id(new ConduitCall('diffusion.looksoon', $params))
        ->setUser($viewer)
        ->execute();

      return id(new AphrontRedirectResponse())->setURI($edit_uri);
    }

    $doc_name = 'Diffusion User Guide: Repository Updates';
    $doc_href = PhabricatorEnv::getDoclink($doc_name);
    $doc_link = phutil_tag(
      'a',
      array(
        'href' => $doc_href,
        'target' => '_blank',
      ),
      $doc_name);

    return $this->newDialog()
      ->setTitle(pht('Update Repository Now'))
      ->appendParagraph(
        pht(
          'Normally, Phabricator automatically updates repositories '.
          'based on how much time has elapsed since the last commit. '.
          'This helps reduce load if you have a large number of mostly '.
          'inactive repositories, which is common.'))
      ->appendParagraph(
        pht(
          'You can manually schedule an update for this repository. The '.
          'daemons will perform the update as soon as possible. This may '.
          'be helpful if you have just made a commit to a rarely used '.
          'repository.'))
      ->appendParagraph(
        pht(
          'To learn more about how Phabricator updates repositories, '.
          'read %s in the documentation.',
          $doc_link))
      ->addCancelButton($edit_uri)
      ->addSubmitButton(pht('Schedule Update'));
  }


}
