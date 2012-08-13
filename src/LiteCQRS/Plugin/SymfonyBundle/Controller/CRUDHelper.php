<?php

namespace LiteCQRS\Plugin\SymfonyBundle\Controller;

use LiteCQRS\Plugin\CRUD\Model\Commands\CreateResourceCommand;
use LiteCQRS\Plugin\CRUD\Model\Commands\UpdateResourceCommand;
use LiteCQRS\Plugin\CRUD\Model\Commands\DeleteResourceCommand;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Helper class to avoid repetition when implementing the controllers
 * for CRUD resources/commands.
 */
class CRUDHelper
{
    private $commandBus;
    private $formFactory;

    public function __construct(CommandBus $commandBus, FormFactoryInterface $formFactory)
    {
        $this->commandBus  = $commandBus;
        $this->formFactory = $formFactory;
    }

    protected function view($data)
    {
        return $data;
    }

    public function handleCreate($class, $formType, Request $request, $originalData = array(), $formOptions = array())
    {
        $form = $this->formFactory->create($formType, $originalData, $formOptions);
        $form->bind($request);

        if (!$form->isValid()) {
            return $this->view(array('form' => $form->createView(), 'data' => $form->getData()));
        }

        $createCommand        = new CreateResourceCommand();
        $createCommand->class = $class;
        $createCommand->data =  $form->getData();

        try {
            $this->commandBus->handle($createCommand);
        } catch(Exception $e) {
            return $this->view(array('form' => $form->createView(), 'data' => $form->getData()));
        }
    }

    public function handleUpdate($class, $id, $formType, Request $request, $originalData = array(), $formOptions = array())
    {
        $form = $this->formFactory->create($formType, $originalData, $formOptions);
        $form->bind($request);

        if (!$form->isValid()) {
            return $this->view(array('form' => $form->createView(), 'data' => $form->getData()));
        }

        $updateCommand        = new UpdateResourceCommand();
        $updateCommand->class = $class;
        $updateCommand->id    = $id;
        $updateCommand->data  = $form->getData();

        try {
            $this->commandBus->handle($updateCommand);
        } catch(Exception $e) {
            return $this->view(array('form' => $form->createView(), 'data' => $form->getData()));
        }
    }

    public function handleDelete($class, $id)
    {
        $deleteCommand        = new DeleteResourceCommand();
        $deleteCommand->class = $class;
        $deleteCommand->id    = $id;

        $this->commandHandlerService->delete($deleteCommand);
    }
}

