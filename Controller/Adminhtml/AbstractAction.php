<?php
namespace MSP\TwoFactorAuth\Controller\Adminhtml;

abstract class AbstractAction extends \Magento\Backend\App\Action
{
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->_isAllowed()) {
            $this->_response->setStatusHeader(403, '1.1', 'Forbidden');
            return $this->_redirect('*/auth/login');
        }

        return parent::dispatch($request);
    }
}
