# Ikarus Logic Model
This package specifies the basic model to run an engine or edit ikarus logics.

### Installation
```bin
$ composer require ikarus/logic-model
```

### Concept
The Ikarus Logic Model describes a workflow. This workflow is setup with four parts:
1.  The project  
    A project contains all information about the whole workflow. An engine can only run one single project.
1.  Scenes  
    Any project must contain one or more scenes. A scene is like a function in programming context.  
    It is recommended to spread the workflow to many scenes for better overview.
1.  Nodes  
    The nodes are bricks with inputs and outputs. A node takes its inputs, executes a task and provide outputs.
1.  Connections
    The connections connect inputs with outputs of the nodes. So now the nodes can communicate with each other.
    
