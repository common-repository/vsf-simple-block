<?php if (!defined('VSF_BLOCK_DIR')) die("Denied");
/**
	Interface for the VSF Simple Block Import / Export classes.
	Contains just constants.
*/
interface IVSFSimpleBlockPort
{
	/** Root element of the import and export file */
	const ROOT_ELEMENT = 'vsfSimpleBlock';
	const XML_HEADER = "Content-Type:text/xml";
}

?>