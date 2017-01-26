<?php

/*
this is for grabbing the ids of current shopping cart items.  Could be used to pass to a new cart upon a successful login. 
 */

               $arrCart = $_ARCHON->Security->Session->ResearchCart->getCart();
               foreach($arrCart->Collections as $CollectionID => $arrObjs)
               {
                  foreach($arrObjs->Content as $CollectionContentID => $obj)
                      echo 'ContentID: '.($CollectionContentID);
                     if($obj instanceof Collection)
                     {
                        $objCollection = $obj;
                        echo 'CollectionIDNoContent: '.$objCollection->ID;
                        unset($objContent);
                     }
                     else
                     {
                        $objCollection = $obj->Collection;
                        echo 'CollectionIDContent: '.$obj->Collection->ID;
                        $objContent = $obj;
                     }
                  if($objCollection->RepositoryID == $_REQUEST['repositoryid'])
                  {
                     $objAppointment->dbRelateMaterials($CollectionID, $CollectionContentID);
                     $_ARCHON->Security->Session->ResearchCart->deleteFromCart($CollectionID, $CollectionContentID);
                  }
               }
?>
