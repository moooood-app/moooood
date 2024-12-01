from typing import Union, List, Dict, Any, Protocol

class Processor(Protocol):
    def process(self, entry: str) -> Union[Dict[str, Any], List[Any]]:
        """Processes entry and returns the metadata."""
        pass
