<?php namespace Indikator\Popup\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Indikator\Popup\Models\Campaigns;
use Indikator\Popup\Models\Themes as Item;
use File;
use Flash;
use Lang;

class Themes extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['indikator.popup.themes'];

    public $bodyClass = 'compact-container';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Indikator.Popup', 'popup', 'themes');
    }

    public function onActivate()
    {
        if ($this->isSelected()) {
            $this->changeStatus(post('checked'), 2, 1);
            $this->setMessage('activate');
        }

        return $this->listRefresh();
    }

    public function onDeactivate()
    {
        if ($this->isSelected()) {
            $this->changeStatus(post('checked'), 1, 2);
            $this->setMessage('deactivate');
        }

        return $this->listRefresh();
    }

    public function onRemove()
    {
        if ($this->isSelected()) {
            foreach (post('checked') as $itemId) {
                if (!$item = Item::whereId($itemId)) {
                    continue;
                }

                Campaigns::where('theme', $itemId)->update([
                    'theme' => 0
                ]);

                File::delete(base_path().'/plugins/indikator/popup/assets/css/theme-'.$itemId.'.css');

                $item->delete();
            }

            $this->setMessage('remove');
        }

        return $this->listRefresh();
    }

    /**
     * @return bool
     */
    private function isSelected()
    {
        return ($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds);
    }

    /**
     * @param $action
     */
    private function setMessage($action)
    {
        Flash::success(Lang::get('indikator.popup::lang.flash.'.$action));
    }

    /**
     * @param $post
     * @param $from
     * @param $to
     */
    private function changeStatus($post, $from, $to)
    {
        foreach ($post as $itemId) {
            if (!$item = Item::where('status', $from)->whereId($itemId)) {
                continue;
            }

            $item->update(['status' => $to]);
        }
    }
}
