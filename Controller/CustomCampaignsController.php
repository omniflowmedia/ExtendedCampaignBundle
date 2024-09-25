<?php

namespace MauticPlugin\SurgeExtendedCampaignBundle\Controller;

use Mautic\CampaignBundle\Controller\CampaignController;
use Doctrine\ORM\EntityNotFoundException;
use MauticPlugin\SurgeExtendedCampaignBundle\Model\CustomCampaignModel;
use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CoreBundle\Model\FormModel;
use MauticPlugin\SurgeExtendedCampaignBundle\Entity\CustomCampaign;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use MauticPlugin\SurgeExtendedCampaignBundle\Form\Type\CustomCampaignType;
use MauticPlugin\SurgeExtendedCampaignBundle\Integration\Configuration;


class CustomCampaignsController extends CampaignController
{

    /**
     * Generate's new form and processes post data.
     *
     * @return JsonResponse | RedirectResponse | Response
     */
    public function newAction()
    {
        $customCampaign = new CustomCampaign();

        /** @var CampaignModel $model */
        $model    = $this->getModel('campaign');
         
        /** @var CustomCampaignModel $model */
        $customCampaignModel = $this->getModel('extendedcampaigns.customcampaigns');
        

        $campaign = $model->getEntity();
        $campaign->type = $customCampaign->getType();

        if (!$this->get('mautic.security')->isGranted('campaign:campaigns:create')) {
            return $this->accessDenied();
        }

        //set the page we came from
        $page = $this->get('session')->get('mautic.campaign.page', 1);

        $options = $this->getEntityFormOptions();
        $action  = $this->generateUrl('mautic_campaign_action', ['objectAction' => 'new']);
        $form    = $this->createForm(CustomCampaignType::class, $campaign, ['action' => $action]);

        ///Check for a submitted form and process it
        $isPost = 'POST' === $this->request->getMethod();
        $this->beforeFormProcessed($campaign, $form, 'new', $isPost);

        if ($isPost) {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    if ($valid = $this->beforeEntitySave($campaign, $form, 'new')) {
                        $campaign->setDateModified(new \DateTime());
                        $type = $campaign->type;
                        $this->appendTypeToName($campaign, $type);
                        $model->saveEntity($campaign);

                        $customCampaign->setType($campaign->type);
                        $customCampaign->setId($campaign->getId());
                        $customCampaignModel->saveEntity($customCampaign);

                        $this->afterEntitySave($campaign, $form, 'new', $valid);

                        if (method_exists($this, 'viewAction')) {
                            $viewParameters = ['objectId' => $campaign->getId(), 'objectAction' => 'view'];
                            $returnUrl      = $this->generateUrl('mautic_campaign_action', $viewParameters);
                            $template       = 'MauticCampaignBundle:Campaign:view';
                        } else {
                            $viewParameters = ['page' => $page];
                            $returnUrl      = $this->generateUrl('mautic_campaign_index', $viewParameters);
                            $template       = 'MauticCampaignBundle:Campaign:index';
                        }
                    }
                }

                $this->afterFormProcessed($valid, $campaign, $form, 'new');
            } else {
                $viewParameters = ['page' => $page];
                $returnUrl      = $this->generateUrl($this->getIndexRoute(), $viewParameters);
                $template       = 'MauticCampaignBundle:Campaign:index';
            }

            $passthrough = [
                'mauticContent' => 'cammpaign',
            ];

            if ($isInPopup = isset($form['updateSelect'])) {
                $template    = false;
                $passthrough = array_merge(
                    $passthrough,
                    $this->getUpdateSelectParams($form['updateSelect']->getData(), $campaign)
                );
            }

            if ($cancelled || ($valid && !$this->isFormApplied($form))) {
                if ($isInPopup) {
                    $passthrough['closeModal'] = true;
                }

                return $this->postActionRedirect(
                    $this->getPostActionRedirectArguments(
                        [
                            'returnUrl'       => $returnUrl,
                            'viewParameters'  => $viewParameters,
                            'contentTemplate' => $template,
                            'passthroughVars' => $passthrough,
                            'entity'          => $campaign,
                        ],
                        'new'
                    )
                );
            } elseif ($valid && $this->isFormApplied($form)) {
                return $this->editAction($campaign->getId(), true);
            }
        }

        $delegateArgs = [
            'viewParameters' => [
                'permissionBase'  => $model->getPermissionBase(),
                'mauticContent'   => 'campaign',
                'actionRoute'     => 'mautic_campaign_action',
                'indexRoute'      => 'mautic_campaign_index',
                'tablePrefix'     => 'c',
                'modelName'       => 'campaign',
                'translationBase' => $this->getTranslationBase(),
                'tmpl'            => $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index',
                'entity'          => $campaign,
                'form'            => $this->getFormView($form, 'new'),
            ],
            'contentTemplate' => 'SurgeExtendedCampaignBundle:Campaign:form.html.php',
            'passthroughVars' => [
                'mauticContent' => 'campaign',
                'route'         => $this->generateUrl(
                    'mautic_campaign_action',
                    [
                        'objectAction' => (!empty($valid) ? 'edit' : 'new'), //valid means a new form was applied
                        'objectId'     => ($campaign) ? $campaign->getId() : 0,
                    ]
                ),
                'validationError' => $this->getFormErrorForBuilder($form),
            ],
            'entity' => $campaign,
            'form'   => $form,
        ];

        return $this->delegateView(
            $this->getViewArguments($delegateArgs, 'new')
        );
    }

    /**
     * Generate's clone form and processes post data.
     *
     * @param int  $objectId
     * @param bool $ignorePost
     *
     * @return Response
     */
    public function cloneAction($objectId, $ignorePost = false)
    {
        $model  = $this->getModel($this->getModelName());
        $entity = $model->getEntity($objectId);

        if (null != $entity) {
            if (!$this->checkActionPermission('clone', $entity)) {
                return $this->accessDenied();
            }

            $newEntity = clone $entity;

            if ($arguments = $this->afterEntityClone($newEntity, $entity)) {
                return call_user_func_array([$this, 'editAction'], $arguments);
            } else {
                return $this->editAction($newEntity, true);
            }
        }

        return $this->newAction();
    }

    public function editAction($objectId, $ignorePost = false, bool $isNew = false)
    {
        $isClone = false;
        $model   = $this->getModel($this->getModelName());
        $customCampaignModel = $this->getModel('extendedcampaigns.customcampaigns');

        if (!$model instanceof FormModel) {
            throw new \Exception(get_class($model).' must extend '.FormModel::class);
        }

        $entity = $this->getFormEntity('edit', $objectId, $isClone);

        $customCampaign = $this->getCampaignCstm($objectId);

        if(is_null($customCampaign)){
            $customCampaign = new CustomCampaign();
            $type = $this->getTypeFromName($entity->getName());

            $configuration = $this->get('mautic.extendedcampaigns.configuration');

            $options = $configuration->getTypeOptions();
            $raw_type = '';
            foreach ($options as $key => $value) {
                if ($value == $type) {
                    $raw_type = $key;
                    break;
                }
            }
            $customCampaign->setType($raw_type);
        }

        $entity->type = $customCampaign->getType();

        //set the return URL
        $returnUrl      = $this->generateUrl($this->getIndexRoute());
        $page           = $this->get('session')->get('mautic.'.$this->getSessionBase().'.page', 1);
        $viewParameters = ['page' => $page];

        $template = $this->getControllerBase().':'.$this->getPostActionControllerAction('edit');

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => $viewParameters,
            'contentTemplate' => $template,
            'passthroughVars' => [
                'mauticContent' => $this->getJsLoadMethodPrefix(),
            ],
            'entity' => $entity,
        ];

        //form not found
        if (null === $entity) {
            return $this->postActionRedirect(
                $this->getPostActionRedirectArguments(
                    array_merge(
                        $postActionVars,
                        [
                            'flashes' => [
                                [
                                    'type'    => 'error',
                                    'msg'     => $this->getTranslatedString('error.notfound'),
                                    'msgVars' => ['%id%' => $objectId],
                                ],
                            ],
                        ]
                    ),
                    'edit'
                )
            );
        } elseif ((!$isClone && !$this->checkActionPermission('edit', $entity)) || ($isClone && !$this->checkActionPermission('create'))) {
            //deny access if the entity is not a clone and don't have permission to edit or is a clone and don't have permission to create
            return $this->accessDenied();
        } elseif (!$isClone && $model->isLocked($entity)) {
            //deny access if the entity is locked
            return $this->isLocked($postActionVars, $entity, $this->getModelName());
        }

        $options = $this->getEntityFormOptions();
        $action  = $this->generateUrl($this->getActionRoute(), ['objectAction' => 'edit', 'objectId' => $objectId]);
        $form    = $this->createForm(CustomCampaignType::class, $entity, ['action' => $action]);

        $isPost = !$ignorePost && 'POST' == $this->request->getMethod();
        $this->beforeFormProcessed($entity, $form, 'edit', $isPost, $objectId, $isClone);

        ///Check for a submitted form and process it
        if ($isPost) {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    if ($valid = $this->beforeEntitySave($entity, $form, 'edit', $objectId, $isClone)) {
                        $type = $entity->type;
                        $this->appendTypeToName($entity, $type);
                        $model->saveEntity($entity, $form->get('buttons')->get('save')->isClicked());

                        $customCampaign->setType($entity->type);
                        $customCampaign->setId($entity->getId());
                        $customCampaignModel->saveEntity($customCampaign);


                        $this->afterEntitySave($entity, $form, 'edit', $valid);


                        $this->addFlash(
                            'mautic.core.notice.updated',
                            [
                                '%name%'      => $entity->getName(),
                                '%menu_link%' => $this->getIndexRoute(),
                                '%url%'       => $this->generateUrl(
                                    $this->getActionRoute(),
                                    [
                                        'objectAction' => 'edit',
                                        'objectId'     => $entity->getId(),
                                    ]
                                ),
                            ]
                        );

                        if ($entity->getId() !== $objectId) {
                            // No longer a clone - this is important for Apply
                            $objectId = $entity->getId();
                        }

                        if (!$this->isFormApplied($form) && method_exists($this, 'viewAction')) {
                            $viewParameters                    = ['objectId' => $objectId, 'objectAction' => 'view'];
                            $returnUrl                         = $this->generateUrl($this->getActionRoute(), $viewParameters);
                            $postActionVars['contentTemplate'] = $this->getControllerBase().':view';
                        }
                    }

                    $this->afterFormProcessed($valid, $entity, $form, 'edit', $isClone);
                }
            } else {
                if (!$isClone) {
                    //unlock the entity
                    $model->unlockEntity($entity);
                }

                $returnUrl = $this->generateUrl($this->getIndexRoute(), $viewParameters);
            }

            if ($cancelled || ($valid && !$this->isFormApplied($form))) {
                return $this->postActionRedirect(
                    $this->getPostActionRedirectArguments(
                        array_merge(
                            $postActionVars,
                            [
                                'returnUrl'      => $returnUrl,
                                'viewParameters' => $viewParameters,
                            ]
                        ),
                        'edit'
                    )
                );
            } elseif ($valid) {
                // Rebuild the form with new action so that apply doesn't keep creating a clone
                $action = $this->generateUrl($this->getActionRoute(), ['objectAction' => 'edit', 'objectId' => $entity->getId()]);
                $form   = $this->createForm(CustomCampaignType::class, $entity, ['action' => $action]);
                $this->beforeFormProcessed($entity, $form, 'edit', false, $isClone);
            }
        } elseif (!$isClone) {
            $model->lockEntity($entity);
        }

        $delegateArgs = [
            'viewParameters' => [
                'permissionBase'  => $this->getPermissionBase(),
                'mauticContent'   => $this->getJsLoadMethodPrefix(),
                'actionRoute'     => $this->getActionRoute(),
                'indexRoute'      => $this->getIndexRoute(),
                'tablePrefix'     => $model->getRepository()->getTableAlias(),
                'modelName'       => $this->getModelName(),
                'translationBase' => $this->getTranslationBase(),
                'tmpl'            => $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index',
                'entity'          => $entity,
                'form'            => $this->getFormView($form, 'edit'),
            ],
            'contentTemplate' => 'SurgeExtendedCampaignBundle:Campaign:form.html.php',
            'passthroughVars' => [
                'mauticContent' => $this->getJsLoadMethodPrefix(),
                'route'         => $this->generateUrl(
                    $this->getActionRoute(),
                    [
                        'objectAction' => 'edit',
                        'objectId'     => $entity->getId(),
                    ]
                ),
                'validationError' => $this->getFormErrorForBuilder($form),
            ],
            'objectId' => $objectId,
            'entity'   => $entity,
        ];

        return $this->delegateView(
            $this->getViewArguments($delegateArgs, 'edit')
        );
    }

    public function deleteAction($objectId)
    {

        $page      = $this->get('session')->get('mautic.'.$this->getSessionBase().'.page', 1);
        $returnUrl = $this->generateUrl($this->getIndexRoute(), ['page' => $page]);
        $flashes   = [];
        $model     = $this->getModel($this->getModelName());
        $customCampaignModel = $this->getModel('extendedcampaigns.customcampaigns');
        $entity    = $model->getEntity($objectId);
        $customCampaign = $this->getCampaignCstm($objectId);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => $this->getControllerBase().':'.$this->getPostActionControllerAction('delete'),
            'passthroughVars' => [
                'mauticContent' => $this->getJsLoadMethodPrefix(),
            ],
            'entity' => $entity,
        ];

        if ('POST' == $this->request->getMethod()) {
            if (null === $entity) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => $this->getTranslatedString('error.notfound'),
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->checkActionPermission('delete', $entity)) {
                return $this->accessDenied();
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, $this->getModelName());
            }

            $model->deleteEntity($entity);
            if(null !== $customCampaign){
                $customCampaignModel->deleteEntity($customCampaign);
            }

            $identifier = $this->get('translator')->trans($entity->getName());
            $flashes[]  = [
                'type'    => 'notice',
                'msg'     => 'mautic.core.notice.deleted',
                'msgVars' => [
                    '%name%' => $identifier,
                    '%id%'   => $objectId,
                ],
            ];
        } //else don't do anything

        return $this->postActionRedirect(
            $this->getPostActionRedirectArguments(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => $flashes,
                    ]
                ),
                'delete'
            )
        );
    }

    public function batchDeleteAction()
    {
        $page      = $this->get('session')->get('mautic.'.$this->getSessionBase().'.page', 1);
        $returnUrl = $this->generateUrl($this->getIndexRoute(), ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => $this->getControllerBase().':'.$this->getPostActionControllerAction('batchDelete'),
            'passthroughVars' => [
                'mauticContent' => $this->getJsLoadMethodPrefix(),
            ],
        ];

        if ('POST' == $this->request->getMethod()) {
            $model     = $this->getModel($this->getModelName());
            $customCampaignModel = $this->getModel('extendedcampaigns.customcampaigns');
            $ids       = json_decode($this->request->query->get('ids', ''));
            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);

                if (null === $entity) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => $this->getTranslatedString('error.notfound'),
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->checkActionPermission('batchDelete', $entity)) {
                    $flashes[] = $this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, $this->getModelName(), true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $entities = $model->deleteEntities($deleteIds);
                $customCampaignModel->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => $this->getTranslatedString('notice.batch_deleted'),
                    'msgVars' => [
                        '%count%' => count($entities),
                    ],
                ];
            }
        } //else don't do anything

        return $this->postActionRedirect(
            $this->getPostActionRedirectArguments(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => $flashes,
                    ]
                ),
                'batchDelete'
            )
        );
    }

    protected function prepareCampaignEventsForEdit($entity, $objectId, $isClone = false)
    {
        //load existing events into session
        $campaignEvents = [];

        $existingEvents = $entity->getEvents()->toArray();
        $translator     = $this->get('translator');
        $dateHelper     = $this->get('mautic.helper.template.date');
        foreach ($existingEvents as $e) {
            $event = $e->convertToArray();

            if ($isClone) {
                $id          = $e->getTempId();
                $event['id'] = $id;
            } else {
                $id = $e->getId();
            }

            unset($event['campaign']);
            unset($event['children']);
            unset($event['parent']);
            unset($event['log']);

            // dd($event);

            $label = false;
            switch ($event['triggerMode']) {
                case 'interval':
                    $label = $translator->trans(
                        'mautic.campaign.connection.trigger.interval.label'.('no' == $event['decisionPath'] ? '_inaction' : ''),
                        [
                            '%number%' => $event['triggerInterval'],
                            '%unit%'   => $translator->trans(
                                'mautic.campaign.event.intervalunit.'.$event['triggerIntervalUnit'],
                                ['%count%' => $event['triggerInterval']]
                            ),
                        ]
                    );
                    break;
                case 'date':
                    $label = $translator->trans(
                        'mautic.campaign.connection.trigger.date.label'.('no' == $event['decisionPath'] ? '_inaction' : ''),
                        [
                            '%full%' => $dateHelper->toFull($event['triggerDate']),
                            '%time%' => $dateHelper->toTime($event['triggerDate']),
                            '%date%' => $dateHelper->toShort($event['triggerDate']),
                        ]
                    );
                    break;
            }
            if ($label) {
                $event['label'] = $label;
            }

            $campaignEvents[$id] = $event;
        }

        $this->modifiedEvents = $this->campaignEvents = $campaignEvents;
        $this->get('session')->set('mautic.campaign.'.$objectId.'.events.modified', $campaignEvents);
    }

    private function getCampaignCstm($campaignId)
    {
        if(empty($campaignId)){
            return null;
        }
        return $this->getModel('extendedcampaigns.customcampaigns')->getEntity($campaignId);
    }

    private function appendTypeToName(Campaign $campaign, $raw_type){

        if(empty($raw_type)){
            return;
        }

         /** @var Configuration */
        $configuration = $this->get('mautic.extendedcampaigns.configuration');

        $options = $configuration->getTypeOptions();

        $type = '';

        foreach($options as $key => $value){
            if($value == $raw_type){
                $type = $key;
                break;
            }
        }

        if(empty($type)){
            return;
        }

        $name = $campaign->getName();
        $appendStr = '[' . $type . ']';

        if($name && strpos($name, '[')){
            $name = substr_replace($name, $appendStr, strpos($name, '['));
        }else{
            $name .= $appendStr;
        }

        $campaign->setName($name);
    }

    private function getTypeFromName($name){

        if($name && strpos($name, '[')){
            $lenght =  strpos($name, ']') - (strpos($name, '[') + 1);
            return substr($name, strpos($name, '[') + 1, $lenght);
        }

        return '';
    }
}
